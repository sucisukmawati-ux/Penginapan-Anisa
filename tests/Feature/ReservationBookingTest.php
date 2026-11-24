<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReservationBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.midtrans.server_key', 'test-server-key');
        Http::fake([
            '*' => Http::response([
                'token' => 'snap-test-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-test-token',
            ], 201),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guest_can_book_a_fan_room_for_three_hours_at_the_configured_rate(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');
        $room = $this->makeRoom(Room::TYPE_FAN);

        $this->actingAs(User::factory()->create(['phone' => '+628123456789']))
            ->post(route('reservations.store'), [
                'room_id' => $room->id,
                'guest_name' => 'Budi Santoso',
                'guest_phone' => '+628123456789',
                'duration_type' => 'three_hours',
                'start_at' => '2026-10-14T10:00',
            ])
            ->assertRedirect();

        $reservation = Reservation::query()->firstOrFail();
        $this->assertSame(100000.0, (float) $reservation->total_amount);
        $this->assertSame('confirmed', $reservation->status);
        $this->assertSame('pending', $reservation->payment_status);
        $this->assertSame('2026-10-14 10:00:00', $reservation->start_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-14 13:00:00', $reservation->end_at->format('Y-m-d H:i:s'));
        $this->assertSame('+628123456789', $reservation->guest_phone);
        $this->assertDatabaseHas('payments', ['reservation_id' => $reservation->id, 'amount' => 100000]);
    }

    public function test_reservation_can_be_created_before_midtrans_is_configured(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');
        config()->set('services.midtrans.server_key', null);
        $room = $this->makeRoom(Room::TYPE_FAN);

        $this->actingAs(User::factory()->create())
            ->post(route('reservations.store'), [
                'room_id' => $room->id,
                'duration_type' => 'three_hours',
                'start_at' => '2026-10-14T10:00',
            ])
            ->assertRedirect();

        $reservation = Reservation::query()->firstOrFail();
        $this->assertSame('pending', $reservation->payment_status);
        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'status' => 'pending',
            'order_id' => null,
        ]);
    }

    public function test_guest_can_book_an_ac_room_for_half_a_day_at_the_configured_rate(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');
        $room = $this->makeRoom(Room::TYPE_AC);

        $this->actingAs(User::factory()->create())
            ->post(route('reservations.store'), [
                'room_id' => $room->id,
                'duration_type' => 'half_day',
                'start_at' => '2026-10-14T10:00',
            ])
            ->assertRedirect();

        $reservation = Reservation::query()->firstOrFail();
        $this->assertSame(200000.0, (float) $reservation->total_amount);
        $this->assertSame('2026-10-14 22:00:00', $reservation->end_at->format('Y-m-d H:i:s'));
    }

    public function test_overnight_booking_uses_fixed_check_in_and_checkout_times_and_nightly_rate(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');
        $room = $this->makeRoom(Room::TYPE_AC);

        $this->actingAs(User::factory()->create())
            ->post(route('reservations.store'), [
                'room_id' => $room->id,
                'duration_type' => 'overnight',
                'start_at' => '2026-10-14T10:00',
                'nights' => 2,
            ])
            ->assertRedirect();

        $reservation = Reservation::query()->firstOrFail();
        $this->assertSame('2026-10-14 14:00:00', $reservation->start_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-16 12:00:00', $reservation->end_at->format('Y-m-d H:i:s'));
        $this->assertSame(700000.0, (float) $reservation->total_amount);
    }

    public function test_room_cannot_be_reserved_during_an_existing_booking(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');
        $user = User::factory()->create();
        $room = $this->makeRoom(Room::TYPE_FAN);
        $payload = [
            'room_id' => $room->id,
            'duration_type' => 'three_hours',
            'start_at' => '2026-10-14T10:00',
        ];

        $this->actingAs($user)->post(route('reservations.store'), $payload)->assertRedirect();

        $this->actingAs(User::factory()->create())
            ->from(route('rooms.show', $room))
            ->post(route('reservations.store'), $payload)
            ->assertSessionHasErrors('start_at');

        $this->assertSame(1, Reservation::query()->count());
    }

    public function test_room_can_be_reserved_again_immediately_after_the_previous_booking_ends(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');
        $room = $this->makeRoom(Room::TYPE_FAN);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('reservations.store'), [
            'room_id' => $room->id,
            'duration_type' => 'three_hours',
            'start_at' => '2026-10-14T10:00',
        ])->assertRedirect();

        $this->actingAs(User::factory()->create())
            ->post(route('reservations.store'), [
                'room_id' => $room->id,
                'duration_type' => 'three_hours',
                'start_at' => '2026-10-14T13:00',
            ])
            ->assertRedirect();

        $this->assertSame(2, Reservation::query()->count());
    }

    public function test_room_types_expose_the_expected_rates_for_each_duration(): void
    {
        $fanRoom = $this->makeRoom(Room::TYPE_FAN);
        $acRoom = $this->makeRoom(Room::TYPE_AC);

        $this->assertSame(100000, $fanRoom->rateFor('three_hours'));
        $this->assertSame(150000, $fanRoom->rateFor('half_day'));
        $this->assertSame(250000, $fanRoom->rateFor('overnight'));
        $this->assertSame(150000, $acRoom->rateFor('three_hours'));
        $this->assertSame(200000, $acRoom->rateFor('half_day'));
        $this->assertSame(350000, $acRoom->rateFor('overnight'));
    }

    public function test_room_rates_fall_back_safely_for_missing_or_invalid_data(): void
    {
        $room = Room::make([
            'name' => 'Kamar Tanpa Tipe',
            'slug' => 'kamar-tanpa-tipe',
            'description' => 'Kamar lama tanpa tipe yang valid.',
            'room_type' => null,
            'price_per_night' => null,
            'capacity' => 2,
            'status' => 'available',
        ]);

        $this->assertSame(250000, $room->rateFor(''));
        $this->assertSame(250000, $room->rateFor('invalid_duration'));
        $this->assertSame('Rp 0', $room->formattedPrice());
    }

    public function test_room_detail_displays_configured_rates_and_duration_choices(): void
    {
        $room = $this->makeRoom(Room::TYPE_AC);

        $this->get(route('rooms.show', $room))
            ->assertOk()
            ->assertSee('3 jam')
            ->assertSee('Setengah hari (12 jam)')
            ->assertSee('Rp 150.000')
            ->assertSee('Rp 200.000')
            ->assertSee('Rp 350.000')
            ->assertSee('waktu otomatis disesuaikan ke pukul 14.00')
            ->assertDontSee('2 tamu');
    }

    public function test_room_detail_shows_future_reserved_dates_and_times(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');
        $room = $this->makeRoom(Room::TYPE_FAN);

        Reservation::create([
            'user_id' => User::factory()->create()->id,
            'room_id' => $room->id,
            'guest_name' => 'Tamu Tes',
            'check_in' => '2026-10-14',
            'check_out' => '2026-10-14',
            'nights' => 1,
            'duration_type' => 'three_hours',
            'start_at' => '2026-10-14 10:00:00',
            'end_at' => '2026-10-14 13:00:00',
            'status' => 'confirmed',
        ]);

        $this->get(route('rooms.show', $room))
            ->assertOk()
            ->assertSee('Jadwal kamar yang sudah dipesan')
            ->assertSee('14/10/2026')
            ->assertSee('10:00 – 14/10/2026 13:00');
    }

    public function test_database_seeder_creates_the_configured_room_inventory(): void
    {
        $this->seed();

        $this->assertSame(28, Room::query()->count());
        $this->assertSame(6, Room::query()->where('room_type', Room::TYPE_AC)->count());
        $this->assertSame(22, Room::query()->where('room_type', Room::TYPE_FAN)->count());
        $this->assertSame(
            ['Kamar AC 01', 'Kamar AC 02', 'Kamar AC 03', 'Kamar AC 04', 'Kamar AC 05', 'Kamar AC 06'],
            Room::query()->where('room_type', Room::TYPE_AC)->orderBy('name')->pluck('name')->all(),
        );
        $this->assertSame(
            [
                asset('images/kamarAC1.jpg'),
                asset('images/kamarAC2.jpg'),
                asset('images/kamarAC3.jpg'),
                asset('images/kamarAC4.jpg'),
                asset('images/kamarAC5.jpg'),
                asset('images/kamarAC6.jpg'),
            ],
            Room::query()->where('room_type', Room::TYPE_AC)->orderBy('name')->get()->map->imageUrl()->all(),
        );
    }

    private function makeRoom(string $roomType): Room
    {
        return Room::create([
            'name' => 'Kamar Tes',
            'slug' => 'kamar-tes-'.uniqid(),
            'description' => 'Kamar pengujian.',
            'room_type' => $roomType,
            'price_per_night' => Room::RATES[$roomType]['overnight'],
            'capacity' => 2,
            'status' => 'available',
        ]);
    }
}
