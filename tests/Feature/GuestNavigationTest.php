<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_unauthenticated_visitors_can_view_rooms_without_dashboard_navigation(): void
    {
        $this->get(route('rooms.index'))
            ->assertOk()
            ->assertSee('Navigasi pengunjung')
            ->assertDontSee('Menu Penginapan');
    }

    public function test_unknown_room_slug_shows_a_link_back_to_the_room_list(): void
    {
        $this->get(route('rooms.show', ['room' => 'kamar-kipas-angin-01']))
            ->assertNotFound()
            ->assertSee('Kamar tidak ditemukan')
            ->assertSee(route('rooms.index'), false);
    }

    public function test_room_cards_show_local_room_details_and_rates(): void
    {
        $facility = Facility::create(['name' => 'AC']);
        $room = Room::create([
            'name' => 'Kamar AC 01',
            'slug' => 'kamar-ac-01',
            'description' => 'Kamar nyaman dengan pendingin udara.',
            'room_type' => Room::TYPE_AC,
            'price_per_night' => Room::RATES[Room::TYPE_AC]['overnight'],
            'capacity' => 2,
            'status' => 'available',
        ]);
        $room->facilities()->attach($facility);
        Room::create([
            'name' => 'Kamar Kipas Angin 03',
            'slug' => 'kamar-kipas-angin-03',
            'description' => 'Kamar kipas angin untuk pengujian.',
            'room_type' => Room::TYPE_FAN,
            'price_per_night' => Room::RATES[Room::TYPE_FAN]['overnight'],
            'capacity' => 2,
            'status' => 'available',
        ]);

        $this->get(route('rooms.index'))
            ->assertOk()
            ->assertSee('Kamar AC 01')
            ->assertSee('Kamar Kipas Angin 03')
            ->assertSee('Kamar nyaman dengan pendingin udara.')
            ->assertSee('Rp 150.000')
            ->assertSee('AC')
            ->assertSee('images/kamar1.jpeg')
            ->assertDontSee('Swimming Pool')
            ->assertDontSee('Breakfast')
            ->assertDontSee('area kerja');

        $this->get(route('rooms.show', $room))
            ->assertOk()
            ->assertSee('Kamar AC')
            ->assertSee('Kamar nyaman dengan pendingin udara.')
            ->assertDontSee('Swimming Pool');
    }

    public function test_room_search_filters_type_and_carries_booking_choices_to_room_detail(): void
    {
        $fanRoom = Room::create([
            'name' => 'Kamar Fan Tes',
            'slug' => 'kamar-fan-tes',
            'description' => 'Kamar kipas untuk pengujian.',
            'room_type' => Room::TYPE_FAN,
            'price_per_night' => Room::RATES[Room::TYPE_FAN]['overnight'],
            'capacity' => 2,
            'status' => 'available',
        ]);
        $acRoom = Room::create([
            'name' => 'Kamar AC Tes',
            'slug' => 'kamar-ac-tes',
            'description' => 'Kamar AC untuk pengujian.',
            'room_type' => Room::TYPE_AC,
            'price_per_night' => Room::RATES[Room::TYPE_AC]['overnight'],
            'capacity' => 2,
            'status' => 'available',
        ]);
        $startAt = now()->addDay()->setTime(14, 0)->format('Y-m-d\TH:i');
        $search = [
            'room_type' => Room::TYPE_AC,
            'duration_type' => 'overnight',
            'start_at' => $startAt,
            'nights' => 2,
        ];

        $this->get(route('rooms.index', $search))
            ->assertOk()
            ->assertSee('Kamar AC')
            ->assertDontSee('Kamar Kipas Angin')
            ->assertSee('duration_type=overnight')
            ->assertSee('start_at='.urlencode($startAt))
            ->assertSee('nights=2')
            ->assertDontSee($fanRoom->slug);

        $this->get(route('rooms.show', ['room' => $acRoom->slug] + array_diff_key($search, ['room_type' => true])))
            ->assertOk()
            ->assertSee('value="overnight" selected', false)
            ->assertSee('value="'.$startAt.'"', false)
            ->assertSee('value="2"', false);
    }

    public function test_room_search_excludes_booked_rooms_only_for_overlapping_times(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');
        $room = Room::create([
            'name' => 'Kamar Jadwal',
            'slug' => 'kamar-jadwal',
            'description' => 'Kamar untuk uji ketersediaan.',
            'room_type' => Room::TYPE_FAN,
            'price_per_night' => Room::RATES[Room::TYPE_FAN]['overnight'],
            'capacity' => 2,
            'status' => 'available',
        ]);
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
            'status' => 'pending',
        ]);

        $this->get(route('rooms.index', [
            'start_at' => '2026-10-14T10:00',
            'duration_type' => 'three_hours',
        ]))
            ->assertOk()
            ->assertSee('0 kamar ditemukan')
            ->assertSee('Kamar tidak tersedia pada jadwal tersebut')
            ->assertDontSee('kamar-jadwal');

        $this->get(route('rooms.index', [
            'start_at' => '2026-10-14T13:00',
            'duration_type' => 'three_hours',
        ]))
            ->assertOk()
            ->assertSee('1 kamar ditemukan')
            ->assertSee('kamar-jadwal');
    }

    public function test_guest_pages_use_guest_navigation_in_the_shared_dashboard_layout(): void
    {
        $guest = User::factory()->create(['role' => UserRole::GUEST]);
        $this->actingAs($guest);

        $this->get(route('rooms.index'))
            ->assertOk()
            ->assertSee('Cari Kamar')
            ->assertSee('Reservasi Saya')
            ->assertDontSee('Kelola Kamar')
            ->assertDontSee('Dashboard')
            ->assertDontSee('2 tamu');

        $this->get(route('reservations.index'))
            ->assertOk()
            ->assertSee('Reservasi Saya')
            ->assertSee('Cari Kamar')
            ->assertDontSee('Kelola Pengguna');
    }
}
