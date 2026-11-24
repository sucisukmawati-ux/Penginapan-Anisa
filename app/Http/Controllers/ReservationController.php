<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Services\RoomAvailability;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function create(Room $room): RedirectResponse
    {
        return redirect()->route('rooms.show', $room);
    }

    public function index(): View
    {
        $reservations = auth()->user()
            ->reservations()
            ->with('room')
            ->latest()
            ->get();

        return view('reservations.index', compact('reservations'));
    }

    public function show(Reservation $reservation): View
    {
        $user = auth()->user();

        if (! $user->isAdmin() && ! $user->isStaff() && $reservation->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke reservasi ini.');
        }

        $reservation->load(['room.facilities', 'user', 'payment']);

        return view('reservations.show', compact('reservation'));
    }

    public function store(Request $request, RoomAvailability $roomAvailability, WhatsAppService $whatsAppService): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'guest_name' => ['nullable', 'string', 'max:100'],
            'guest_phone' => ['nullable', 'string', 'max:30'],
            'duration_type' => ['required', Rule::in(['three_hours', 'half_day', 'overnight'])],
            'start_at' => ['required', 'date', 'after_or_equal:now'],
            'nights' => ['required_if:duration_type,overnight', 'nullable', 'integer', 'min:1', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $guestName = trim((string) ($validated['guest_name'] ?? auth()->user()->name ?? '')) ?: (auth()->user()->name ?? 'Tamu');
        $guestPhone = trim((string) ($validated['guest_phone'] ?? auth()->user()->phone ?? '')) ?: (auth()->user()->phone ?? '628123456789');

        $nights = 1;

        if ($validated['duration_type'] === 'overnight') {
            $nights = (int) $validated['nights'];
        }

        [$startAt, $endAt] = $roomAvailability->bookingWindow(
            $validated['start_at'],
            $validated['duration_type'],
            $nights,
        );

        if ($startAt->isPast()) {
            throw ValidationException::withMessages([
                'start_at' => 'Waktu mulai harus berada pada waktu sekarang atau setelahnya.',
            ]);
        }

        [$reservation, $room, $totalAmount] = DB::transaction(function () use ($validated, $startAt, $endAt, $nights, $guestName, $guestPhone, $roomAvailability) {
            $room = Room::query()->lockForUpdate()->findOrFail((int) $validated['room_id']);

            if ($room->status !== 'available') {
                throw ValidationException::withMessages([
                    'room_id' => 'Kamar yang dipilih saat ini tidak tersedia.',
                ]);
            }

            if ($roomAvailability->hasConflict($room, $startAt, $endAt)) {
                throw ValidationException::withMessages([
                    'start_at' => 'Kamar sudah dipesan pada waktu tersebut. Silakan pilih waktu atau kamar lain.',
                ]);
            }

            $totalAmount = $room->rateFor($validated['duration_type'])
                * ($validated['duration_type'] === 'overnight' ? $nights : 1);

            $reservation = Reservation::query()->create([
                'user_id' => auth()->id(),
                'room_id' => $room->id,
                'guest_name' => $guestName,
                'guest_phone' => $guestPhone,
                'check_in' => $startAt->toDateString(),
                'check_out' => $endAt->toDateString(),
                'nights' => $nights,
                'duration_type' => $validated['duration_type'],
                'start_at' => $startAt,
                'end_at' => $endAt,
                'total_amount' => $totalAmount,
                'status' => 'confirmed',
                'payment_status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            $reservation->payment()->create([
                'user_id' => auth()->id(),
                'amount' => $totalAmount,
                'status' => 'pending',
            ]);

            return [$reservation, $room, $totalAmount];
        });

        $whatsAppService->send([
            'phone' => $guestPhone,
            'message' => 'Reservasi kamar '.$room->name.' otomatis dikonfirmasi. Total: Rp '.number_format($totalAmount, 0, ',', '.').'.',
        ]);

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reservasi otomatis disetujui karena jadwal tersedia. Silakan lanjutkan proses pembayaran.');
    }

    public function cancel(Reservation $reservation): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isAdmin() && ! $user->isStaff() && $reservation->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan reservasi ini.');
        }

        DB::transaction(function () use ($reservation): void {
            Room::query()->lockForUpdate()->findOrFail($reservation->room_id);
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);

            $reservation->update([
                'status' => 'cancelled',
                'payment_status' => 'cancelled',
            ]);

            if ($reservation->payment) {
                $reservation->payment->update([
                    'status' => 'cancelled',
                ]);
            }
        });

        return back()->with('success', 'Reservasi berhasil dibatalkan.');
    }
}
