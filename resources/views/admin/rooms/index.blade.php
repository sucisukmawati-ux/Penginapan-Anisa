<x-layouts::app :title="__('Kelola Kamar')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-2xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-600">Admin</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900">Kelola Kamar</h1>
            </div>
            <a href="{{ route('admin.rooms.create') }}" class="inline-flex items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Tambah kamar</a>
        </div>

        @if (session('success'))
            <div role="status" class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if ($errors->has('room'))
            <div role="alert" class="rounded-xl bg-rose-50 p-4 text-sm text-rose-800">{{ $errors->first('room') }}</div>
        @endif

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Kamar</th>
                        <th class="px-4 py-3 font-medium">Tipe / tarif menginap</th>
                        <th class="px-4 py-3 font-medium">Kapasitas</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Reservasi</th>
                        <th class="px-4 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rooms as $room)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $room->name }}</p>
                                <p class="text-xs text-slate-500">{{ $room->slug }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $room->room_type === 'ac' ? 'AC' : 'Kipas angin' }} · {{ $room->formattedPrice() }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $room->capacity }} tamu</td>
                            <td class="px-4 py-3 text-slate-700">{{ ucfirst($room->status) }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $room->reservations_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.rooms.edit', $room) }}" class="font-semibold text-sky-700 hover:underline">Edit</a>
                                    @if ($room->reservations_count === 0)
                                        <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Hapus kamar ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-semibold text-rose-700 hover:underline">Hapus</button>
                                        </form>
                                    @else
                                        <button
                                            type="button"
                                            class="cursor-not-allowed font-semibold text-slate-400"
                                            disabled
                                            title="Kamar memiliki riwayat reservasi dan tidak dapat dihapus."
                                            aria-label="Hapus kamar tidak tersedia karena memiliki riwayat reservasi"
                                        >Hapus</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">Belum ada kamar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $rooms->links() }}
    </div>
</x-layouts::app>
