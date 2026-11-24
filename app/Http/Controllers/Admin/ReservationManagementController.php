<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\RoomAvailability;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservationManagementController extends Controller
{
    public function updateStatus(Request $request, Reservation $reservation, RoomAvailability $roomAvailability): RedirectResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled']),
            ],
        ]);

        DB::transaction(function () use ($reservation, $validated, $roomAvailability) {
            $room = Room::query()->lockForUpdate()->findOrFail($reservation->room_id);
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);

            if (! in_array($validated['status'], ['cancelled', 'checked_out'], true)) {
                $startAt = Carbon::parse($reservation->start_at ?? $reservation->check_in->copy()->setTime(14, 0));
                $endAt = Carbon::parse($reservation->end_at ?? $reservation->check_out->copy()->setTime(12, 0));

                if ($roomAvailability->hasConflict($room, $startAt, $endAt, $reservation->id)) {
                    throw ValidationException::withMessages([
                        'status' => 'Status tidak dapat diaktifkan karena jadwal kamar bertabrakan dengan reservasi lain.',
                    ]);
                }
            }

            $reservation->update(['status' => $validated['status']]);
        });

        return redirect()->route('admin.reservations')
            ->with('success', 'Status reservasi berhasil diperbarui.');
    }
}
