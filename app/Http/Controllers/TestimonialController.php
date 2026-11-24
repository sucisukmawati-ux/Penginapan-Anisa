<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TestimonialController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('testimonials')) {
            return back()->withErrors([
                'content' => 'Sistem testimoni belum siap. Silakan hubungi admin.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string', 'min:10', 'max:1000'],
            'rating' => ['required', 'integer', 'between:1,5'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'content.required' => 'Testimoni wajib diisi.',
            'content.min' => 'Testimoni minimal 10 karakter.',
            'content.max' => 'Testimoni maksimal 1.000 karakter.',
            'rating.required' => 'Silakan pilih rating bintang.',
            'rating.integer' => 'Rating harus berupa angka.',
            'rating.between' => 'Rating harus antara 1 sampai 5 bintang.',
        ]);

        Testimonial::query()->create($validated);

        $rating = (int) $validated['rating'];

        return redirect('/#testimoni')->with(
            'success',
            'Terima kasih! Testimoni Anda berhasil dikirim dengan rating '.str_repeat('★', $rating)." ({$rating}/5)."
        );
    }
}
