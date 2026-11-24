<x-layouts::app :title="__('Detail Reservasi')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-2xl">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-600">Reservasi</p>
                    <h1 class="mt-2 text-3xl font-black text-slate-900">{{ $reservation->room->name }}</h1>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700">
                    {{ $reservation->status }}
                </span>
            </div>

            @if (session('error'))
                <div role="alert" class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Check-in</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">{{ $reservation->start_at?->format('d M Y H:i') ?? $reservation->check_in->format('d M Y') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Check-out</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">{{ $reservation->end_at?->format('d M Y H:i') ?? $reservation->check_out->format('d M Y') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Durasi</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">{{ ['three_hours' => '3 jam', 'half_day' => 'Setengah hari', 'overnight' => $reservation->nights.' malam'][$reservation->duration_type ?? 'overnight'] }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Total</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <h2 class="text-lg font-bold text-slate-900">Detail pembayaran</h2>
                    <dl class="mt-4 space-y-3 text-sm text-slate-600">
                        <div class="flex items-center justify-between gap-3">
                            <dt>Status pembayaran</dt>
                            <dd class="font-semibold text-slate-900">{{ ucfirst($reservation->payment_status) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt>Order ID</dt>
                            <dd class="font-semibold text-slate-900">{{ $reservation->midtrans_order_id }}</dd>
                        </div>
                        @if ($reservation->payment)
                            <div class="flex items-center justify-between gap-3">
                                <dt>Metode</dt>
                                <dd class="font-semibold text-slate-900">{{ ucfirst($reservation->payment->payment_type ?? 'transfer') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <h2 class="text-lg font-bold text-slate-900">Catatan</h2>
                    <p class="mt-4 text-sm leading-6 text-slate-600">
                        {{ $reservation->notes ?: 'Tidak ada catatan tambahan dari tamu.' }}
                    </p>
                </div>
            </div>

            @if ($reservation->payment_status !== 'paid' && $reservation->status !== 'cancelled')
                <form action="{{ route('reservations.checkout', $reservation) }}" method="POST" class="mt-8">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-full bg-amber-500 px-5 py-3 text-sm font-bold text-white hover:bg-amber-600">
                        {{ $reservation->payment_status === 'pending' ? 'Bayar sekarang' : 'Coba bayar lagi' }}
                    </button>
                    <p class="mt-2 text-sm text-slate-500">Pembayaran aman diproses melalui Midtrans.</p>
                </form>
            @endif

            @if ($reservation->status !== 'cancelled')
                <form action="{{ route('reservations.cancel', $reservation) }}" method="POST" class="mt-8">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                        Batalkan reservasi
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-layouts::app>
