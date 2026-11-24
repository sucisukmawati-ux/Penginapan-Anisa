<x-layouts::app :title="__('Cari Kamar')">
    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-7 px-4 py-6 sm:px-7 sm:py-9">
        <div class="flex flex-col gap-5 rounded-3xl bg-[#31584a] p-6 text-white shadow-lg shadow-[#26332e]/10 sm:p-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-medium text-white/75 transition hover:text-white">
                    <span aria-hidden="true">&larr;</span> Beranda
                </a>
                <p class="mt-6 text-xs font-bold uppercase tracking-[0.18em] text-[#f0d978]">Penginapan Anisa</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Temukan kamar yang pas</h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-white/80">Bandingkan tipe kamar dan tarifnya, lalu pilih waktu istirahat yang sesuai.</p>
            </div>
            <div class="rounded-2xl border border-white/15 bg-white/10 px-5 py-4 backdrop-blur-sm">
                <p class="text-xs font-medium text-white/70">Pilihan sewa fleksibel</p>
                <p class="mt-1 text-sm font-semibold">3 jam · Setengah hari · Menginap</p>
            </div>
        </div>

        <form action="{{ route('rooms.index') }}" method="GET" class="grid gap-3 rounded-2xl border border-[#e2e7df] bg-white p-4 shadow-sm sm:grid-cols-2 sm:p-5 lg:grid-cols-[1fr_1.2fr_1fr_0.7fr_0.7fr_0.7fr_auto] lg:items-end">
            <label class="block">
                <span class="mb-2 block text-xs font-semibold text-[#59665e]">Tipe kamar</span>
                <select name="room_type" class="min-h-12 w-full rounded-xl border border-[#dfe5dd] bg-white px-3.5 text-sm text-[#26332e] outline-none focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
                    <option value="">Semua tipe kamar</option>
                    <option value="fan" @selected(($filters['room_type'] ?? '') === 'fan')>Kamar kipas angin</option>
                    <option value="ac" @selected(($filters['room_type'] ?? '') === 'ac')>Kamar AC</option>
                </select>
            </label>
            <label class="block">
                <span class="mb-2 block text-xs font-semibold text-[#59665e]">Tanggal &amp; waktu mulai</span>
                <input type="datetime-local" name="start_at" value="{{ $filters['start_at'] ?? '' }}" min="{{ now()->format('Y-m-d\TH:i') }}" class="min-h-12 w-full rounded-xl border border-[#dfe5dd] bg-white px-3.5 text-sm text-[#26332e] outline-none focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
            </label>
            <label class="block">
                <span class="mb-2 block text-xs font-semibold text-[#59665e]">Durasi sewa</span>
                <select name="duration_type" data-duration-select class="min-h-12 w-full rounded-xl border border-[#dfe5dd] bg-white px-3.5 text-sm text-[#26332e] outline-none focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
                    <option value="three_hours" @selected($selectedDuration === 'three_hours')>3 jam</option>
                    <option value="half_day" @selected($selectedDuration === 'half_day')>Setengah hari</option>
                    <option value="overnight" @selected($selectedDuration === 'overnight')>Menginap / malam</option>
                </select>
            </label>
            <div data-nights-field @if($selectedDuration !== 'overnight') hidden @endif>
                <label class="block">
                    <span class="mb-2 block text-xs font-semibold text-[#59665e]">Jumlah malam</span>
                    <input type="number" name="nights" value="{{ $filters['nights'] ?? 1 }}" min="1" max="30" @if($selectedDuration !== 'overnight') disabled @endif class="min-h-12 w-full rounded-xl border border-[#dfe5dd] bg-white px-3.5 text-sm text-[#26332e] outline-none focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
                </label>
            </div>
            <label class="block">
                <span class="mb-2 block text-xs font-semibold text-[#59665e]">Jumlah tamu</span>
                <input type="number" name="guest_count" value="{{ $filters['guest_count'] ?? 2 }}" min="1" max="10" class="min-h-12 w-full rounded-xl border border-[#dfe5dd] bg-white px-3.5 text-sm text-[#26332e] outline-none focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
            </label>
            <label class="block">
                <span class="mb-2 block text-xs font-semibold text-[#59665e]">Jumlah kamar</span>
                <input type="number" name="room_count" value="{{ $filters['room_count'] ?? 1 }}" min="1" max="10" class="min-h-12 w-full rounded-xl border border-[#dfe5dd] bg-white px-3.5 text-sm text-[#26332e] outline-none focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
            </label>
            <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#f0c943] px-6 text-sm font-bold text-[#342d1e] transition hover:bg-[#e6bd36] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#31584a]">
                Cari kamar
            </button>
        </form>

        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#66736b]">Pilihan tersedia</p>
                <h2 class="mt-1 text-xl font-bold text-[#26332e]">{{ $rooms->count() }} kamar ditemukan</h2>
            </div>
            @if (filled($filters['room_type'] ?? null) || filled($filters['start_at'] ?? null))
                <a href="{{ route('rooms.index') }}" class="text-sm font-semibold text-[#31584a] underline decoration-[#31584a]/30 underline-offset-4 hover:decoration-[#31584a]">Hapus filter</a>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
            @forelse ($rooms as $room)
                @php
                    $bookingQuery = array_filter([
                        'duration_type' => $filters['duration_type'] ?? null,
                        'start_at' => $filters['start_at'] ?? null,
                        'nights' => $filters['nights'] ?? null,
                        'guest_count' => $filters['guest_count'] ?? null,
                        'room_count' => $filters['room_count'] ?? null,
                    ], static fn ($value) => filled($value));
                @endphp
                <article class="group overflow-hidden rounded-2xl border border-[#e2e7df] bg-white shadow-sm shadow-[#26332e]/5 transition hover:shadow-lg hover:shadow-[#26332e]/10">
                    <a href="{{ route('rooms.show', ['room' => $room->slug] + $bookingQuery) }}" class="relative block aspect-[4/3] overflow-hidden bg-[#e2ddd1]">
                        <img src="{{ $room->imageUrl() ?? asset('images/kamar'.(($loop->index % 3) + 1).'.jpeg') }}" alt="Foto {{ $room->name }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]" />
                        <span class="absolute left-2.5 top-2.5 rounded-full bg-white px-2.5 py-1 text-[10px] font-bold text-[#31584a] shadow-sm">
                            {{ $room->status === 'available' ? 'Tersedia' : ucfirst($room->status) }}
                        </span>
                    </a>
                    <div class="flex min-w-0 flex-col p-3">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="line-clamp-2 text-sm font-bold text-[#26332e]">{{ $room->name }}</h3>
                            <span class="shrink-0 rounded-full bg-[#f1f4ef] px-2 py-1 text-[10px] font-semibold text-[#31584a]">
                                {{ $room->room_type === 'ac' ? 'AC' : 'Kipas' }}
                            </span>
                        </div>

                        <p class="mt-1.5 line-clamp-2 text-xs leading-4 text-[#66736b]">{{ $room->description }}</p>

                        <div class="mt-3 flex items-end justify-between gap-2 border-t border-[#e6e9e2] pt-3">
                            <div>
                                <p class="text-[10px] text-[#66736b]">{{ ['three_hours' => '3 jam', 'half_day' => 'Setengah hari', 'overnight' => 'Per malam'][$selectedDuration] }}</p>
                                <p class="mt-0.5 text-base font-extrabold text-[#26332e]">Rp {{ number_format($room->rateFor($selectedDuration), 0, ',', '.') }}</p>
                            </div>
                            <a href="{{ route('rooms.show', ['room' => $room->slug] + $bookingQuery) }}" class="inline-flex min-h-9 shrink-0 items-center justify-center rounded-lg bg-[#31584a] px-3 text-xs font-bold text-white transition hover:bg-[#26332e]">
                                Pilih kamar
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-[#c8d2c8] bg-white p-10 text-center">
                    <p class="text-lg font-bold text-[#26332e]">{{ $isSearchingAvailability ? 'Kamar tidak tersedia pada jadwal tersebut' : 'Belum ada kamar untuk pilihan ini' }}</p>
                    <p class="mt-2 text-sm text-[#66736b]">{{ $isSearchingAvailability ? 'Coba waktu, durasi, atau tipe kamar lain untuk melihat pilihan yang tersedia.' : 'Coba tipe kamar yang lain atau hapus filter untuk melihat semua kamar.' }}</p>
                    <a href="{{ route('rooms.index') }}" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#31584a] px-5 text-sm font-bold text-white">Lihat semua kamar</a>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts::app>
