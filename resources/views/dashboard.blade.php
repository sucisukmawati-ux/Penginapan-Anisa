<x-layouts::app :title="__('Dashboard')">
    @php
        $roomTypes = [
            \App\Models\Room::TYPE_FAN => 'Kamar Kipas Angin',
            \App\Models\Room::TYPE_AC => 'Kamar AC',
        ];
        $reservationStatusLabels = [
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'checked_in' => 'Check-in',
            'checked_out' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        $reservationStatusClasses = [
            'pending' => 'bg-amber-100 text-amber-700',
            'confirmed' => 'bg-sky-100 text-sky-700',
            'checked_in' => 'bg-emerald-100 text-emerald-700',
            'checked_out' => 'bg-slate-100 text-slate-700',
            'cancelled' => 'bg-rose-100 text-rose-700',
        ];
    @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-2xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-600">Penginapan Anisa</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Dashboard</h1>
                <p class="mt-1 text-sm text-slate-500">Ringkasan operasional {{ now()->format('d M Y') }}</p>
            </div>
            <a href="{{ route('admin.reservations') }}" class="inline-flex items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                Kelola reservasi
            </a>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-sm font-medium text-slate-500">Pembayaran lunas bulan ini</span>
                <p class="mt-4 text-3xl font-black text-slate-900">Rp {{ number_format((float) $revenueThisMonth, 0, ',', '.') }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ $paidReservationCountThisMonth }} reservasi telah dibayar</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-sm font-medium text-slate-500">Kamar tersedia</span>
                <p class="mt-4 text-3xl font-black text-slate-900">{{ $availableRoomCount }}</p>
                <p class="mt-2 text-sm text-slate-500">Dari {{ $totalRoomCount }} kamar terdaftar</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-sm font-medium text-slate-500">Check-in hari ini</span>
                <p class="mt-4 text-3xl font-black text-slate-900">{{ $checkInsToday }}</p>
                <p class="mt-2 text-sm text-slate-500">Jadwal check-in hari ini</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-sm font-medium text-slate-500">Reservasi menunggu</span>
                <p class="mt-4 text-3xl font-black text-slate-900">{{ $pendingReservationCount }}</p>
                <p class="mt-2 text-sm text-slate-500">Perlu ditindaklanjuti</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.4fr_0.6fr]">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Reservasi terbaru</h2>
                        <p class="text-sm text-slate-500">Lima reservasi yang terakhir dibuat</p>
                    </div>
                    <a href="{{ route('admin.reservations') }}" class="shrink-0 text-sm font-semibold text-slate-700 hover:text-slate-900">Lihat semua</a>
                </div>

                <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 font-medium">Tamu</th>
                                <th class="px-4 py-3 font-medium">Kamar</th>
                                <th class="px-4 py-3 font-medium">Jadwal</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($latestReservations as $reservation)
                                @php
                                    $status = $reservation->status;
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-900">{{ $reservation->guest_display_name }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $reservation->room->name }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                        {{ $reservation->start_at?->format('d M Y H:i') ?? $reservation->check_in->format('d M Y') }}
                                        -
                                        {{ $reservation->end_at?->format('d M Y H:i') ?? $reservation->check_out->format('d M Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $reservationStatusClasses[$status] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ $reservationStatusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-slate-500">Belum ada reservasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900">Ketersediaan kamar</h2>
                <p class="mt-1 text-sm text-slate-500">Berdasarkan jenis kamar</p>

                <div class="mt-6 space-y-5">
                    @foreach ($roomTypes as $type => $label)
                        @php
                            $roomStats = $roomAvailabilityByType->get($type);
                            $roomTotal = (int) ($roomStats->total ?? 0);
                            $roomAvailable = (int) ($roomStats->available ?? 0);
                            $availabilityPercent = $roomTotal > 0 ? (int) round($roomAvailable / $roomTotal * 100) : 0;
                        @endphp
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-sm text-slate-600">
                                <span>{{ $label }}</span>
                                <span class="font-semibold text-slate-900">{{ $roomAvailable }} / {{ $roomTotal }} tersedia</span>
                            </div>
                            <div
                                class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100"
                                role="progressbar"
                                aria-label="Ketersediaan {{ $label }}"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow="{{ $availabilityPercent }}"
                            >
                                <div class="h-full rounded-full bg-amber-500" style="width: {{ $availabilityPercent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 rounded-2xl bg-slate-50 p-4">
                    <p class="text-sm text-slate-500">Reservasi dibuat bulan ini</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ $reservationCountThisMonth }}</p>
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
