<x-layouts::app :title="__('Riwayat Reservasi')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-2xl">
        <div class="flex items-end justify-between gap-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-600">Reservasi</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900">Riwayat Reservasi</h1>
            </div>
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400">
                Cari kamar
            </a>
        </div>

        <div class="space-y-4">
            @forelse ($reservations as $reservation)
                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-lg font-bold text-slate-900">{{ $reservation->room->name }}</p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $reservation->start_at?->format('d M Y H:i') ?? $reservation->check_in->format('d M Y') }}
                                -
                                {{ $reservation->end_at?->format('d M Y H:i') ?? $reservation->check_out->format('d M Y') }}
                                · {{ ['three_hours' => '3 jam', 'half_day' => 'Setengah hari', 'overnight' => $reservation->nights.' malam'][$reservation->duration_type ?? 'overnight'] }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700">
                                {{ $reservation->status }}
                            </span>
                            <a href="{{ route('reservations.show', $reservation) }}" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                    Belum ada reservasi yang dibuat.
                </div>
            @endforelse
        </div>
    </div>
</x-layouts::app>
