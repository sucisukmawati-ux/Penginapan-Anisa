<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_save_a_testimonial_and_see_it_on_the_landing_page(): void
    {
        $response = $this->post(route('testimonials.store'), [
            'name' => 'Siti',
            'content' => 'Penginapan bersih dan pelayanannya sangat ramah.',
            'rating' => 5,
        ]);

        $response->assertRedirect('/#testimoni')
            ->assertSessionHas('success', 'Terima kasih! Testimoni Anda berhasil dikirim dengan rating ★★★★★ (5/5).');
        $this->assertDatabaseHas('testimonials', [
            'name' => 'Siti',
            'content' => 'Penginapan bersih dan pelayanannya sangat ramah.',
            'rating' => 5,
        ]);

        $page = $this->get(route('home'))
            ->assertOk()
            ->assertSee('Penginapan bersih dan pelayanannya sangat ramah.')
            ->assertSee('Siti')
            ->assertSee('5 dari 5 bintang')
            ->assertSee('testimonial-star-filled');
        $this->assertMatchesRegularExpression('/<details data-testimonial-form[^>]*\bopen\b/', $page->getContent());
    }

    public function test_landing_page_does_not_show_sample_testimonials_when_none_have_been_submitted(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Belum ada testimoni tamu.')
            ->assertDontSee('Aisyah')
            ->assertDontSee('Budi');
    }

    public function test_testimonial_requires_a_name_and_at_least_ten_characters_of_content(): void
    {
        $this->from(route('home'))
            ->post(route('testimonials.store'), [
                'name' => '',
                'content' => 'Pendek',
                'rating' => 6,
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors(['name', 'content', 'rating']);

        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_landing_page_shows_testimonials_in_a_horizontal_scroller(): void
    {
        Testimonial::query()->create(['name' => 'Tamu 1', 'content' => 'Testimoni pertama yang cukup panjang.']);
        Testimonial::query()->create(['name' => 'Tamu 2', 'content' => 'Testimoni kedua yang cukup panjang.']);
        Testimonial::query()->create(['name' => 'Tamu 3', 'content' => 'Testimoni ketiga yang cukup panjang.']);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('overflow-x-auto')
            ->assertSee('data-testimonial-form')
            ->assertSee('data-testimonial-toggle')
            ->assertSee('data-testimonial-track')
            ->assertSee('requestAnimationFrame')
            ->assertDontSee('testimonial-card-drift')
            ->assertSee('Testimoni ketiga yang cukup panjang.');
    }
}
