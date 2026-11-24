<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk()->assertSee('Dashboard');
    }

    public function test_guests_cannot_visit_the_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => UserRole::GUEST]);
        $this->actingAs($user);

        $this->get(route('dashboard'))->assertRedirect(route('rooms.index'));
    }

    public function test_admin_dashboard_uses_real_room_reservation_and_payment_data(): void
    {
        Carbon::setTestNow('2026-11-16 10:00:00');
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $guest = User::factory()->create(['role' => UserRole::GUEST]);
        $fanRoom = Room::create([
            'name' => 'Kamar Kipas Angin 01',
            'slug' => 'kamar-kipas-angin-01',
            'description' => 'Kamar kipas untuk pengujian.',
            'room_type' => Room::TYPE_FAN,
            'price_per_night' => Room::RATES[Room::TYPE_FAN]['overnight'],
            'capacity' => 2,
            'status' => 'available',
        ]);
        $acRoom = Room::create([
            'name' => 'Kamar AC 01',
            'slug' => 'kamar-ac-01',
            'description' => 'Kamar AC untuk pengujian.',
            'room_type' => Room::TYPE_AC,
            'price_per_night' => Room::RATES[Room::TYPE_AC]['overnight'],
            'capacity' => 2,
            'status' => 'maintenance',
        ]);
        $pendingReservation = Reservation::create([
            'user_id' => $guest->id,
            'room_id' => $fanRoom->id,
            'guest_name' => 'Tamu Hari Ini',
            'check_in' => '2026-11-16',
            'check_out' => '2026-11-17',
            'start_at' => '2026-11-16 14:00:00',
            'end_at' => '2026-11-17 12:00:00',
            'nights' => 1,
            'duration_type' => 'overnight',
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
        $paidReservation = Reservation::create([
            'user_id' => $guest->id,
            'room_id' => $acRoom->id,
            'guest_name' => 'Tamu Lunas',
            'check_in' => '2026-11-15',
            'check_out' => '2026-11-16',
            'start_at' => '2026-11-15 14:00:00',
            'end_at' => '2026-11-16 12:00:00',
            'nights' => 1,
            'duration_type' => 'overnight',
            'total_amount' => 350000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
        Payment::create([
            'reservation_id' => $paidReservation->id,
            'user_id' => $guest->id,
            'amount' => 350000,
            'status' => 'paid',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pembayaran lunas bulan ini')
            ->assertSee('Rp 350.000')
            ->assertSee('1 / 1 tersedia')
            ->assertSee('Kamar Kipas Angin 01')
            ->assertSee('Tamu Hari Ini')
            ->assertDontSee('Family Suite')
            ->assertDontSee('82%');

        $this->assertDatabaseHas('reservations', [
            'id' => $pendingReservation->id,
            'status' => 'pending',
        ]);
    }
}
