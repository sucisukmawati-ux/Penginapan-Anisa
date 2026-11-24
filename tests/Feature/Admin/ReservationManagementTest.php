<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_open_reservation_management_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::STAFF]));

        $this->get(route('admin.reservations'))->assertOk();
    }

    public function test_staff_can_update_reservation_status(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);
        $reservation = $this->makeReservation();

        $this->actingAs($staff)
            ->patch(route('admin.reservations.update-status', $reservation), ['status' => 'confirmed'])
            ->assertRedirect(route('admin.reservations'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'confirmed',
            'payment_status' => 'pending',
        ]);
    }

    public function test_guest_cannot_update_reservation_status(): void
    {
        $guest = User::factory()->create(['role' => UserRole::GUEST]);
        $reservation = $this->makeReservation();

        $this->actingAs($guest)
            ->patch(route('admin.reservations.update-status', $reservation), ['status' => 'confirmed'])
            ->assertForbidden();
    }

    public function test_invalid_reservation_status_is_rejected(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);
        $reservation = $this->makeReservation();

        $this->actingAs($staff)
            ->patch(route('admin.reservations.update-status', $reservation), ['status' => 'paid'])
            ->assertSessionHasErrors('status');
    }

    public function test_staff_cannot_activate_a_reservation_that_overlaps_another_booking(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);
        $reservation = $this->makeReservation();
        $reservation->update(['status' => 'cancelled']);

        Reservation::create([
            'user_id' => $reservation->user_id,
            'room_id' => $reservation->room_id,
            'check_in' => $reservation->check_in,
            'check_out' => $reservation->check_out,
            'nights' => 1,
            'total_amount' => 300000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $this->actingAs($staff)
            ->patch(route('admin.reservations.update-status', $reservation), ['status' => 'confirmed'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
    }

    private function makeReservation(): Reservation
    {
        $user = User::factory()->create(['role' => UserRole::GUEST]);
        $room = Room::create([
            'name' => 'Kamar Reservasi',
            'slug' => 'kamar-reservasi-management',
            'description' => 'Kamar untuk tes status reservasi.',
            'price_per_night' => 300000,
            'capacity' => 2,
            'status' => 'available',
        ]);

        return Reservation::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'check_in' => '2026-11-01',
            'check_out' => '2026-11-02',
            'nights' => 1,
            'total_amount' => 300000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }
}
