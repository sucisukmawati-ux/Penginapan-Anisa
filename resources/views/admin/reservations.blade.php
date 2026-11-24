<x-layouts::app :title="__('Kelola Reservasi')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-2xl">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-600">Operasional</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Kelola Reservasi</h1>
        </div>

        @if (session('success'))
            <div role="status" class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">Tamu</th>
                            <th class="px-4 py-3 font-medium">Kamar</th>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Total</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Pembayaran</th>
                            <th class="px-4 py-3 font-medium">Ubah status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($reservations as $reservation)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $reservation->guest_display_name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $reservation->room->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $reservation->start_at?->format('d M Y H:i') ?? $reservation->check_in->format('d M Y') }} - {{ $reservation->end_at?->format('d M Y H:i') ?? $reservation->check_out->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700">
                                        {{ $reservation->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ ucfirst($reservation->payment_status) }}</td>
                                <td class="px-4 py-3">
                                    <form action="{{ route('admin.reservations.update-status', $reservation) }}" method="POST" class="flex min-w-52 items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" aria-label="Status reservasi {{ $reservation->id }}" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs">
                                            @foreach (['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'] as $status)
                                                <option value="{{ $status }}" @selected($reservation->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="font-semibold text-sky-700 hover:underline">Simpan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-slate-500">Belum ada reservasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $reservations->links() }}
        </div>
    </div>
</x-layouts::app>
