<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Services\MidtransService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function checkout(Reservation $reservation, MidtransService $midtransService): RedirectResponse
    {
        $this->authorizeReservation($reservation);

        abort_if($reservation->status === 'cancelled', 409, 'Reservasi yang dibatalkan tidak dapat dibayar.');
        abort_if($reservation->payment_status === 'paid', 409, 'Reservasi ini sudah dibayar.');

        if (! config('services.midtrans.server_key')) {
            return redirect()->route('reservations.show', $reservation)
                ->with('error', 'Pembayaran online belum dikonfigurasi. Pengelola perlu mengisi MIDTRANS_SERVER_KEY di file .env.');
        }

        $payment = $reservation->payment;
        $redirectUrl = data_get($payment?->midtrans_response, 'redirect_url');

        if ($payment?->status === 'pending' && is_string($redirectUrl) && $redirectUrl !== '') {
            return redirect()->away($redirectUrl);
        }

        $orderId = 'INV-'.$reservation->id.'-'.Str::upper(Str::random(10));

        try {
            $transaction = $midtransService->createTransaction([
                'order_id' => $orderId,
                'amount' => $reservation->total_amount,
                'item_details' => [[
                    'id' => 'room-'.$reservation->room_id,
                    'price' => (int) round((float) $reservation->total_amount),
                    'quantity' => 1,
                    'name' => mb_substr('Reservasi '.$reservation->room->name, 0, 50),
                ]],
                'customer_details' => [
                    'first_name' => mb_substr($reservation->guest_display_name, 0, 50),
                    'email' => $reservation->user->email,
                ],
                'callbacks' => [
                    'finish' => route('reservations.show', $reservation),
                ],
            ]);
        } catch (ConnectionException|RequestException $exception) {
            Log::error('Midtrans checkout request failed.', [
                'order_id' => $orderId,
                'exception' => $exception->getMessage(),
            ]);

            return redirect()->route('reservations.show', $reservation)
                ->with('error', 'Pembayaran belum dapat diproses oleh Midtrans. Silakan coba beberapa saat lagi.');
        }

        DB::transaction(function () use ($reservation, $payment, $orderId, $transaction): void {
            $lockedReservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            $lockedReservation->update(['midtrans_order_id' => $orderId]);

            $lockedPayment = $payment
                ? Payment::query()->lockForUpdate()->findOrFail($payment->id)
                : $lockedReservation->payments()->make(['user_id' => $lockedReservation->user_id]);
            $lockedPayment->fill([
                'order_id' => $transaction['order_id'],
                'amount' => $lockedReservation->total_amount,
                'status' => 'pending',
                'midtrans_response' => $transaction['midtrans_response'],
            ]);
            $lockedPayment->save();
        });

        return redirect()->away($transaction['redirect_url']);
    }

    public function notification(Request $request, MidtransService $midtransService): JsonResponse
    {
        $notification = $request->validate([
            'order_id' => ['required', 'string'],
            'status_code' => ['required', 'string'],
            'gross_amount' => ['required', 'string'],
            'signature_key' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string'],
            'payment_type' => ['nullable', 'string'],
            'fraud_status' => ['nullable', 'string'],
        ]);

        abort_unless($midtransService->verifyNotification($notification), 403, 'Signature notifikasi Midtrans tidak valid.');

        $payment = Payment::query()
            ->where('order_id', $notification['order_id'])
            ->firstOrFail();

        abort_unless(
            (int) round((float) $notification['gross_amount']) === (int) round((float) $payment->amount),
            403,
            'Nominal notifikasi Midtrans tidak sesuai.',
        );

        $status = $this->paymentStatus($notification);

        DB::transaction(function () use ($payment, $notification, $status): void {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $lockedReservation = Reservation::query()->lockForUpdate()->findOrFail($lockedPayment->reservation_id);
            $response = $lockedPayment->midtrans_response ?? [];
            $response['notification'] = $notification;
            $effectiveStatus = in_array($lockedPayment->status, ['paid', 'refunded'], true) && $status !== 'refunded'
                ? $lockedPayment->status
                : $status;

            $lockedPayment->update([
                'transaction_id' => $notification['transaction_id'] ?? $lockedPayment->transaction_id,
                'payment_type' => $notification['payment_type'] ?? $lockedPayment->payment_type,
                'status' => $effectiveStatus,
                'midtrans_response' => $response,
            ]);

            if ($lockedReservation->midtrans_order_id === $lockedPayment->order_id
                || in_array($effectiveStatus, ['paid', 'refunded'], true)) {
                if (! in_array($lockedReservation->payment_status, ['paid', 'refunded'], true) || $effectiveStatus === 'refunded') {
                    $lockedReservation->update(['payment_status' => $effectiveStatus]);
                }
            }
        });

        return response()->json(['message' => 'Notifikasi pembayaran diterima.']);
    }

    /**
     * @param  array<string, mixed>  $notification
     */
    private function paymentStatus(array $notification): string
    {
        return match ($notification['transaction_status']) {
            'settlement' => 'paid',
            'capture' => ($notification['fraud_status'] ?? null) === 'accept' ? 'paid' : 'pending',
            'pending' => 'pending',
            'cancel' => 'cancelled',
            'expire' => 'expired',
            'deny' => 'failed',
            'refund', 'partial_refund' => 'refunded',
            default => 'pending',
        };
    }

    private function authorizeReservation(Reservation $reservation): void
    {
        $user = auth()->user();

        abort_unless(
            $user->isAdmin() || $user->isStaff() || $reservation->user_id === $user->id,
            403,
            'Anda tidak memiliki akses ke reservasi ini.',
        );
    }
}
