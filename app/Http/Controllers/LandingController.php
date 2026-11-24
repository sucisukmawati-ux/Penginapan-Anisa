<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $testimonials = Schema::hasTable('testimonials')
            ? Testimonial::query()->latest()->get()
            : collect();

        $availableRooms = Room::query()
            ->available()
            ->selectRaw('room_type, COUNT(*) as room_count')
            ->groupBy('room_type')
            ->pluck('room_count', 'room_type');
        $uploadedRoomImages = Room::query()
            ->whereNotNull('image_url')
            ->where('image_url', '!=', '')
            ->orderBy('id')
            ->get(['room_type', 'image_url'])
            ->groupBy('room_type')
            ->map(static fn ($rooms): ?string => $rooms->first()->imageUrl());

        $roomOptions = collect([
            [
                'type' => Room::TYPE_FAN,
                'label' => 'Kamar Kipas Angin',
                'short_label' => 'Kipas angin',
                'image' => 'kamar1.jpeg',
                'description' => 'Pilihan kamar dengan kipas angin untuk waktu istirahat Anda.',
            ],
            [
                'type' => Room::TYPE_AC,
                'label' => 'Kamar AC',
                'short_label' => 'Air Conditioner (AC)',
                'image' => 'kamar2.jpeg',
                'description' => 'Pilihan kamar dengan pendingin udara untuk waktu istirahat Anda.',
            ],
        ])->map(function (array $option) use ($availableRooms, $uploadedRoomImages): array {
            $option['available_count'] = (int) ($availableRooms->get($option['type']) ?? 0);
            $option['uploaded_image'] = $uploadedRoomImages->get($option['type']);
            $option['rates'] = Room::RATES[$option['type']];

            return $option;
        });

        return view('landing', compact('testimonials', 'roomOptions'));
    }
}
