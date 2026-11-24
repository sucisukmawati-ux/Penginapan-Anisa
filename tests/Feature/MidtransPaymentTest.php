<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.midtrans.server_key', 'test-server-key');
    }

    public function test_owner_can_start_checkout_for_a_pending_reservation(): void
    {
        Http::fake([
            '*' => Http::response([
                'token' => 'snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token',
            ], 201),
        ]);

        $user = User::factory()->create();
        $reservation = $this->makeReservation($user);

        $this->actingAs($user)
            ->post(route('reservations.checkout', $reservation))
            ->assertRedirect('https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token');

        $payment = Payment::query()->firstOrFail();
        $this->assertSame('pending', $payment->status);
        $this->assertSame('snap-token', $payment->midtrans_response['token']);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'midtrans_order_id' => $payment->order_id,
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            && $request['transaction_details']['gross_amount'] === 100000);
    }

    public function test_checkout_reports_missing_midtrans_configuration_without_losing_reservation(): void
    {
        config()->set('services.midtrans.server_key', null);
        $user = User::factory()->create();
        $reservation = $this->makeReservation($user);
        $reservation->payment()->create([
            'user_id' => $user->id,
            'amount' => 100000,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('reservations.checkout', $reservation))
            ->assertRedirect(route('reservations.show', $reservation))
            ->assertSessionHas('error');

        $this->assertSame('pending', $reservation->fresh()->payment_status);
        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'order_id' => null,
        ]);
    }

    public function test_gateway_failure_returns_to_reservation_with_a_clear_error(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $user = User::factory()->create();
        $reservation = $this->makeReservation($user);
        $reservation->payment()->create([
            'user_id' => $user->id,
            'amount' => 100000,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('reservations.checkout', $reservation))
            ->assertRedirect(route('reservations.show', $reservation))
            ->assertSessionHas('error', 'Pembayaran belum dapat diproses oleh Midtrans. Silakan coba beberapa saat lagi.');

        $this->assertSame('pending', $reservation->fresh()->payment_status);
        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'order_id' => null,
        ]);
    }

    public function test_signed_settlement_notification_marks_payment_paid(): void
    {
        $user = User::factory()->create();
        $reservation = $this->makeReservation($user);
        $payment = $reservation->payments()->create([
            'user_id' => $user->id,
            'order_id' => 'INV-TEST-1',
            'amount' => 100000,
            'status' => 'pending',
        ]);
        $notification = [
            'order_id' => $payment->order_id,
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-transaction-1',
            'payment_type' => 'bank_transfer',
        ];
        $notification['signature_key'] = hash(
            'sha512',
            $notification['order_id'].$notification['status_code'].$notification['gross_amount'].'test-server-key',
        );

        $this->postJson(route('midtrans.notifications'), $notification)->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
            'transaction_id' => 'midtrans-transaction-1',
        ]);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'payment_status' => 'paid',
        ]);
    }

    public function test_invalid_notification_signature_is_rejected(): void
    {
        $user = User::factory()->create();
        $reservation = $this->makeReservation($user);
        $reservation->payments()->create([
            'user_id' => $user->id,
            'order_id' => 'INV-TEST-2',
            'amount' => 100000,
            'status' => 'pending',
        ]);

        $this->postJson(route('midtrans.notifications'), [
            'order_id' => 'INV-TEST-2',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_status' => 'settlement',
            'signature_key' => 'invalid',
        ])->assertForbidden();

        $this->assertSame('pending', $reservation->fresh()->payment_status);
    }

    private function makeReservation(User $user): Reservation
    {
        $room = Room::create([
            'name' => 'Kamar Pembayaran',
            'slug' => 'kamar-pembayaran-'.uniqid(),
            'description' => 'Kamar pengujian.',
            'room_type' => Room::TYPE_FAN,
            'price_per_night' => 100000,
            'capacity' => 2,
            'status' => 'available',
        ]);

        return Reservation::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'guest_name' => $user->name,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDay()->toDateString(),
            'nights' => 1,
            'duration_type' => 'overnight',
            'start_at' => now(),
            'end_at' => now()->addDay(),
            'total_amount' => 100000,
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'midtrans_order_id' => 'INV-TEST-1',
        ]);
    }
}
