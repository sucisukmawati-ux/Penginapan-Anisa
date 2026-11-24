<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }

    public function test_homepage_has_scroll_reveal_animations_for_sections_and_cards(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('scroll-reveal-enabled')
            ->assertSee('data-scroll-reveal')
            ->assertSee('IntersectionObserver')
            ->assertSee('prefers-reduced-motion');
    }

    public function test_homepage_footer_links_to_penginapan_anisa_social_profiles(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://www.tiktok.com/@penginapan_anisa', false)
            ->assertSee('https://www.instagram.com/penginapan_anisa/', false)
            ->assertSee('TikTok')
            ->assertSee('Instagram')
            ->assertSee('@penginapan_anisa');
    }

    public function test_homepage_booking_buttons_link_to_the_room_list(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Cek ketersediaan kamar')
            ->assertSee('href="'.route('rooms.index').'"', false)
            ->assertDontSee('href="#booking"', false);
    }

    public function test_homepage_booking_section_shows_room_inventory_and_duration_rates(): void
    {
        foreach ([Room::TYPE_FAN => 21, Room::TYPE_AC => 7] as $type => $count) {
            for ($number = 1; $number <= $count; $number++) {
                Room::query()->create([
                    'name' => sprintf('Kamar %s %02d', $type, $number),
                    'slug' => sprintf('kamar-%s-%02d', $type, $number),
                    'description' => 'Kamar untuk pengujian.',
                    'room_type' => $type,
                    'price_per_night' => Room::RATES[$type]['overnight'],
                    'capacity' => 2,
                    'status' => 'available',
                ]);
            }
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kamar Kipas Angin')
            ->assertSee('21 kamar tersedia')
            ->assertSee('Kamar AC')
            ->assertSee('7 kamar tersedia')
            ->assertSee('3 jam')
            ->assertSee('Setengah hari')
            ->assertSee('Menginap / malam')
            ->assertSee('Rp 100.000')
            ->assertSee('Rp 150.000')
            ->assertSee('Rp 200.000')
            ->assertSee('Rp 250.000')
            ->assertSee('Rp 350.000')
            ->assertSee('Tarif menginap')
            ->assertSee('href="'.route('rooms.index').'"', false);
    }

    public function test_homepage_shows_only_currently_available_room_counts(): void
    {
        Room::query()->create([
            'name' => 'Kamar Kipas 01',
            'slug' => 'kamar-kipas-01',
            'description' => 'Kamar untuk pengujian.',
            'room_type' => Room::TYPE_FAN,
            'price_per_night' => Room::RATES[Room::TYPE_FAN]['overnight'],
            'capacity' => 2,
            'status' => 'available',
        ]);
        Room::query()->create([
            'name' => 'Kamar AC 01',
            'slug' => 'kamar-ac-01',
            'description' => 'Kamar untuk pengujian.',
            'room_type' => Room::TYPE_AC,
            'price_per_night' => Room::RATES[Room::TYPE_AC]['overnight'],
            'capacity' => 2,
            'status' => 'maintenance',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('1 kamar tersedia')
            ->assertSee('0 kamar tersedia')
            ->assertDontSee('21 kamar tersedia')
            ->assertDontSee('7 kamar tersedia')
            ->assertDontSee('Wi-Fi cepat')
            ->assertDontSee('24/7');
    }
}
