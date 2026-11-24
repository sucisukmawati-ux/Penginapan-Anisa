<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\RoomAvailability;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuestRoomController extends Controller
{
    public function index(Request $request, RoomAvailability $roomAvailability): View
    {
        $filters = $request->validate([
            'room_type' => ['nullable', Rule::in(Room::ROOM_TYPES)],
            'duration_type' => ['nullable', Rule::in(['three_hours', 'half_day', 'overnight'])],
            'start_at' => ['nullable', 'date', 'after_or_equal:now'],
            'nights' => ['nullable', 'integer', 'min:1', 'max:30'],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:10'],
            'room_count' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $roomsQuery = Room::query()
            ->with('facilities')
            ->where('status', 'available')
            ->orderBy('price_per_night');

        if (filled($filters['room_type'] ?? null)) {
            $roomsQuery->where('room_type', $filters['room_type']);
        }

        if (filled($filters['guest_count'] ?? null)) {
            $roomsQuery->where('capacity', '>=', (int) $filters['guest_count']);
        }

        $rooms = $roomsQuery->get();
        $selectedDuration = $filters['duration_type'] ?? 'three_hours';
        $isSearchingAvailability = filled($filters['start_at'] ?? null);

        if ($isSearchingAvailability) {
            [$startAt, $endAt] = $roomAvailability->bookingWindow(
                $filters['start_at'],
                $selectedDuration,
                (int) ($filters['nights'] ?? 1),
            );
            $rooms = $roomAvailability->availableRooms($rooms, $startAt, $endAt);
        }

        return view('rooms.index', compact('rooms', 'filters', 'selectedDuration', 'isSearchingAvailability'));
    }

    public function show(Room $room, RoomAvailability $roomAvailability): View
    {
        $room->load('facilities');
        $reservedWindows = $roomAvailability->reservedWindows($room);

        return view('rooms.show', compact('room', 'reservedWindows'));
    }
}
