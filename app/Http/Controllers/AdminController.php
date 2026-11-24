<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user || (! $user->isAdmin() && ! $user->isStaff())) {
            return redirect()->route('rooms.index');
        }

        $today = today();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $roomAvailabilityByType = Room::query()
            ->select('room_type')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS available', ['available'])
            ->groupBy('room_type')
            ->get()
            ->keyBy('room_type');
        $availableRoomCount = $roomAvailabilityByType->sum(
            static fn ($roomStats): int => (int) $roomStats->available,
        );
        $totalRoomCount = $roomAvailabilityByType->sum(
            static fn ($roomStats): int => (int) $roomStats->total,
        );
        $paidPaymentsThisMonth = Payment::query()
            ->where('status', 'paid')
            ->whereBetween('updated_at', [$monthStart, $monthEnd]);
        $revenueThisMonth = (clone $paidPaymentsThisMonth)->sum('amount');
        $paidReservationCountThisMonth = (clone $paidPaymentsThisMonth)
            ->distinct()
            ->count('reservation_id');
        $checkInsToday = Reservation::query()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($today) {
                $query->whereDate('start_at', $today)
                    ->orWhere(function ($query) use ($today) {
                        $query->whereNull('start_at')
                            ->whereDate('check_in', $today);
                    });
            })
            ->count();
        $pendingReservationCount = Reservation::query()
            ->where('status', 'pending')
            ->count();
        $reservationCountThisMonth = Reservation::query()
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();
        $latestReservations = Reservation::query()
            ->with(['room', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'availableRoomCount',
            'checkInsToday',
            'latestReservations',
            'paidReservationCountThisMonth',
            'pendingReservationCount',
            'reservationCountThisMonth',
            'revenueThisMonth',
            'roomAvailabilityByType',
            'totalRoomCount',
        ));
    }

    public function reservations(): View
    {
        $reservations = Reservation::query()
            ->with(['user', 'room', 'payment'])
            ->latest()
            ->paginate(10);

        return view('admin.reservations', compact('reservations'));
    }
}
