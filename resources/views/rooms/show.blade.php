<x-layouts::app :title="$room->name">
    <div class="mx-auto flex h-full w-full max-w-6xl flex-1 flex-col gap-5 rounded-2xl sm:gap-7">
        <a href="{{ route('rooms.index') }}" class="inline-flex w-fit items-center gap-2 text-sm font-medium text-[#66736b] transition hover:text-[#31584a]">
            <span aria-hidden="true">&larr;</span> Kembali ke daftar kamar
        </a>

        <div class="grid items-start gap-4 lg:grid-cols-[1.15fr_0.85fr] lg:gap-5">
            <div class="space-y-4">
                <div class="relative overflow-hidden rounded-2xl border border-[#e2e7df] bg-[#e2ddd1] shadow-md shadow-[#26332e]/10 sm:rounded-3xl">
                    <img src="{{ $room->imageUrl() ?? asset('images/'.($room->room_type === 'ac' ? 'kamar2.jpeg' : 'kamar1.jpeg')) }}" alt="{{ $room->room_type === 'ac' ? 'Kamar dengan AC' : 'Kamar dengan kipas angin' }}" class="h-44 w-full object-cover object-[center_65%] sm:h-52 lg:h-60" />
                    <div class="absolute inset-0 bg-gradient-to-t from-[#18231f]/45 via-transparent to-transparent"></div>
                    <span class="absolute bottom-4 left-4 rounded-full border border-white/30 bg-[#26332e]/70 px-4 py-2 text-xs font-semibold text-white backdrop-blur-sm sm:bottom-6 sm:left-6">
                        {{ $room->room_type === 'ac' ? 'Kamar ber-AC' : 'Kamar kipas angin' }}
                    </span>
                </div>

                <section class="flex h-full flex-col rounded-2xl border border-[#e2e7df] bg-white p-4 shadow-sm shadow-[#26332e]/5 sm:rounded-3xl sm:p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#a76f54]">Penginapan Anisa · Kamar</p>
                            <h1 class="mt-1 font-serif text-2xl font-semibold leading-tight text-[#26332e] sm:text-3xl">{{ $room->name }}</h1>
                        </div>
                        <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-200">
                            {{ $room->status === 'available' ? 'Tersedia' : ucfirst($room->status) }}
                        </span>
                    </div>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-[#66736b]">
                        {{ $room->description }}
                    </p>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div class="border-t border-[#e6e9e2] pt-3">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-[#66736b]">Tarif kamar</h2>
                            <dl class="mt-2 divide-y divide-[#e2e7df] rounded-xl bg-[#f6f7f3] px-3">
                                @foreach (['three_hours' => '3 jam', 'half_day' => 'Setengah hari (12 jam)', 'overnight' => 'Menginap (per malam)'] as $duration => $label)
                                    <div class="flex items-center justify-between gap-3 py-2 text-sm">
                                        <dt class="text-[#66736b]">{{ $label }}</dt>
                                        <dd class="font-semibold text-[#26332e]">Rp {{ number_format($room->rateFor($duration), 0, ',', '.') }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>

                        <section aria-labelledby="room-schedule-heading" class="rounded-xl border border-amber-200 bg-amber-50 p-2.5">
                            <h2 id="room-schedule-heading" class="text-sm font-semibold text-amber-950">Jadwal kamar yang sudah dipesan</h2>
                            @if (count($reservedWindows))
                                <ul class="mt-1.5 space-y-1.5 text-sm text-amber-900">
                                    @foreach ($reservedWindows as $window)
                                        <li class="rounded-lg bg-white/70 px-2.5 py-1.5">
                                            <span class="font-semibold">{{ $window['start_at']->format('d/m/Y') }}</span>
                                            <span class="block text-xs">
                                                {{ $window['start_at']->format('H:i') }} – {{ $window['end_at']->format('d/m/Y H:i') }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="mt-1.5 text-xs leading-4 text-amber-900">Silakan pilih waktu di luar jadwal tersebut. Ketersediaan akan diperiksa kembali saat Anda memesan.</p>
                            @else
                                <p class="mt-1 text-sm text-amber-900">Belum ada jadwal pemesanan mendatang untuk kamar ini.</p>
                            @endif
                        </section>
                    </div>
                </section>
            </div>

            <aside class="rounded-2xl border border-[#e2e7df] bg-white p-4 shadow-sm shadow-[#26332e]/5 sm:rounded-3xl sm:p-5 lg:sticky lg:top-6">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#a76f54]">Reservasi</p>
                <h2 class="mt-1 font-serif text-xl font-semibold text-[#26332e]">Pesan kamar ini</h2>

                <form action="{{ route('reservations.store') }}" method="POST" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="room_id" value="{{ $room->id }}">

                    <div>
                        <label for="guest_name" class="mb-1 block text-xs font-semibold text-[#46554d]">Nama tamu</label>
                        <input id="guest_name" type="text" name="guest_name" value="{{ old('guest_name', auth()->user()?->name ?? '') }}" maxlength="100" required class="min-h-10 w-full rounded-xl border border-[#d8ddd5] bg-white px-3 py-2 text-sm text-[#26332e] outline-none transition placeholder:text-[#92988f] focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15" placeholder="Masukkan nama tamu">
                        @error('guest_name') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="guest_phone" class="mb-1 block text-xs font-semibold text-[#46554d]">Nomor telepon</label>
                        <input id="guest_phone" type="tel" name="guest_phone" value="{{ old('guest_phone', auth()->user()?->phone ?? '') }}" maxlength="30" inputmode="tel" class="min-h-10 w-full rounded-xl border border-[#d8ddd5] bg-white px-3 py-2 text-sm text-[#26332e] outline-none transition placeholder:text-[#92988f] focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15" placeholder="Masukkan nomor telepon">
                        @error('guest_phone') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="duration_type" class="mb-1 block text-xs font-semibold text-[#46554d]">Durasi sewa</label>
                        <select id="duration_type" name="duration_type" required class="min-h-10 w-full rounded-xl border border-[#d8ddd5] bg-white px-3 py-2 text-sm text-[#26332e] outline-none transition focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
                            <option value="three_hours" @selected(old('duration_type', request('duration_type', 'three_hours')) === 'three_hours')>3 jam</option>
                            <option value="half_day" @selected(old('duration_type', request('duration_type')) === 'half_day')>Setengah hari (12 jam)</option>
                            <option value="overnight" @selected(old('duration_type', request('duration_type')) === 'overnight')>Menginap (per malam)</option>
                        </select>
                    </div>

                    <div>
                        <label for="start_at" class="mb-1 block text-xs font-semibold text-[#46554d]">Tanggal dan waktu mulai</label>
                        <input id="start_at" type="datetime-local" name="start_at" value="{{ old('start_at', request('start_at')) }}" min="{{ now()->format('Y-m-d\TH:i') }}" required class="min-h-10 w-full min-w-0 rounded-xl border border-[#d8ddd5] bg-white px-3 py-2 text-sm text-[#26332e] outline-none transition focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
                        <p class="mt-1 text-[11px] leading-4 text-[#788276]">Untuk menginap, waktu otomatis disesuaikan ke pukul 14.00.</p>
                        @error('start_at') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                    </div>

                    <div id="overnight-field" hidden>
                        <label for="nights" class="mb-1 block text-xs font-semibold text-[#46554d]">Jumlah malam</label>
                        <input id="nights" type="number" name="nights" value="{{ old('nights', request('nights', 1)) }}" min="1" max="30" class="min-h-10 w-full rounded-xl border border-[#d8ddd5] bg-white px-3 py-2 text-sm text-[#26332e] outline-none transition focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15">
                        <p class="mt-1 text-[11px] leading-4 text-[#788276]">Check-in pukul 14.00 dan check-out pukul 12.00.</p>
                        @error('nights') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="notes" class="mb-1 block text-xs font-semibold text-[#46554d]">Catatan <span class="font-normal text-[#788276]">(opsional)</span></label>
                        <textarea id="notes" name="notes" rows="2" class="w-full resize-y rounded-xl border border-[#d8ddd5] bg-white px-3 py-2 text-sm text-[#26332e] outline-none transition placeholder:text-[#92988f] focus:border-[#31584a] focus:ring-2 focus:ring-[#31584a]/15" placeholder="Permintaan tambahan..."></textarea>
                    </div>

                    <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-full bg-[#26332e] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#3d5148] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#26332e]">
                        Lanjutkan Reservasi
                    </button>
                </form>
                <script>
                    const durationSelect = document.getElementById('duration_type');
                    const overnightField = document.getElementById('overnight-field');
                    const nightsInput = document.getElementById('nights');
                    const startAtInput = document.getElementById('start_at');
                    const regularStartAtMin = startAtInput.min;

                    function formatDateTimeLocal(date) {
                        const pad = (value) => String(value).padStart(2, '0');

                        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
                    }

                    function updateOvernightField() {
                        const isOvernight = durationSelect.value === 'overnight';
                        overnightField.hidden = !isOvernight;
                        nightsInput.required = isOvernight;

                        if (!isOvernight) {
                            startAtInput.min = regularStartAtMin;

                            return;
                        }

                        const earliestCheckIn = new Date();
                        earliestCheckIn.setHours(14, 0, 0, 0);

                        if (earliestCheckIn <= new Date()) {
                            earliestCheckIn.setDate(earliestCheckIn.getDate() + 1);
                        }

                        const overnightMin = formatDateTimeLocal(earliestCheckIn);
                        startAtInput.min = overnightMin;

                        if (startAtInput.value) {
                            const selectedDate = startAtInput.value.slice(0, 10);
                            const checkInDate = `${selectedDate}T14:00`;
                            startAtInput.value = checkInDate < overnightMin ? overnightMin : checkInDate;
                        }
                    }

                    durationSelect.addEventListener('change', updateOvernightField);
                    startAtInput.addEventListener('change', updateOvernightField);
                    updateOvernightField();
                </script>
            </aside>
        </div>
    </div>
</x-layouts::app>
