<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RoomAvailability
{
    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function bookingWindow(string $startAt, string $durationType, int $nights = 1): array
    {
        $start = Carbon::parse($startAt);

        if ($durationType === 'overnight') {
            $start->setTime(14, 0);

            return [$start, $start->copy()->addDays($nights)->setTime(12, 0)];
        }

        $hours = $durationType === 'half_day' ? 12 : 3;

        return [$start, $start->copy()->addHours($hours)];
    }

    /**
     * @param  Collection<int, Room>  $rooms
     * @return Collection<int, Room>
     */
    public function availableRooms(Collection $rooms, Carbon $startAt, Carbon $endAt, ?int $exceptReservationId = null): Collection
    {
        if ($rooms->isEmpty()) {
            return $rooms;
        }

        $reservations = Reservation::query()
            ->whereIn('room_id', $rooms->modelKeys())
            ->whereNotIn('status', ['cancelled', 'checked_out'])
            ->when($exceptReservationId !== null, fn ($query) => $query->whereKeyNot($exceptReservationId))
            ->where(function ($query) use ($startAt, $endAt) {
                $query->where(function ($query) use ($startAt, $endAt) {
                    $query->whereNotNull('start_at')
                        ->where('start_at', '<', $endAt)
                        ->where('end_at', '>', $startAt);
                })->orWhere(function ($query) use ($startAt, $endAt) {
                    $query->whereNull('start_at')
                        ->whereDate('check_in', '<=', $endAt->toDateString())
                        ->whereDate('check_out', '>=', $startAt->toDateString());
                });
            })
            ->get(['room_id', 'start_at', 'end_at', 'check_in', 'check_out']);

        $unavailableRoomIds = $reservations
            ->filter(function (Reservation $reservation) use ($startAt, $endAt): bool {
                if ($reservation->start_at && $reservation->end_at) {
                    return $reservation->start_at->lt($endAt)
                        && $reservation->end_at->gt($startAt);
                }

                $existingStart = $reservation->check_in->copy()->setTime(14, 0);
                $existingEnd = $reservation->check_out->copy()->setTime(12, 0);

                return $startAt->lt($existingEnd) && $endAt->gt($existingStart);
            })
            ->pluck('room_id')
            ->unique();

        return $rooms->reject(
            static fn (Room $room): bool => $unavailableRoomIds->contains($room->getKey()),
        )->values();
    }

    public function hasConflict(Room $room, Carbon $startAt, Carbon $endAt, ?int $exceptReservationId = null): bool
    {
        return $this->availableRooms(new Collection([$room]), $startAt, $endAt, $exceptReservationId)->isEmpty();
    }

    /**
     * @return array<int, array{start_at: Carbon, end_at: Carbon}>
     */
    public function reservedWindows(Room $room): array
    {
        $now = now();

        return Reservation::query()
            ->where('room_id', $room->getKey())
            ->whereNotIn('status', ['cancelled', 'checked_out'])
            ->where(function ($query) use ($now) {
                $query->where(function ($query) use ($now) {
                    $query->whereNotNull('start_at')
                        ->whereNotNull('end_at')
                        ->where('end_at', '>', $now);
                })->orWhere(function ($query) use ($now) {
                    $query->whereNull('start_at')
                        ->whereDate('check_out', '>=', $now->toDateString());
                });
            })
            ->get(['start_at', 'end_at', 'check_in', 'check_out'])
            ->map(function (Reservation $reservation): array {
                return [
                    'start_at' => Carbon::parse($reservation->start_at ?? $reservation->check_in->copy()->setTime(14, 0)),
                    'end_at' => Carbon::parse($reservation->end_at ?? $reservation->check_out->copy()->setTime(12, 0)),
                ];
            })
            ->filter(fn (array $window): bool => $window['end_at']->gt($now))
            ->sortBy(fn (array $window): int => $window['start_at']->getTimestamp())
            ->values()
            ->all();
    }
}
