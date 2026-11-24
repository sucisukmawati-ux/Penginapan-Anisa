<x-layouts::app :title="__('Kelola Fasilitas')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-2xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-600">Admin</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900">Kelola Fasilitas</h1>
            </div>
            <a href="{{ route('admin.facilities.create') }}" class="inline-flex items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Tambah fasilitas</a>
        </div>

        @if (session('success'))
            <div role="status" class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Fasilitas</th>
                        <th class="px-4 py-3 font-medium">Deskripsi</th>
                        <th class="px-4 py-3 font-medium">Digunakan kamar</th>
                        <th class="px-4 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($facilities as $facility)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $facility->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $facility->description ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $facility->rooms_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.facilities.edit', $facility) }}" class="font-semibold text-sky-700 hover:underline">Edit</a>
                                    <form action="{{ route('admin.facilities.destroy', $facility) }}" method="POST" onsubmit="return confirm('Hapus fasilitas ini dari semua kamar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-rose-700 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">Belum ada fasilitas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $facilities->links() }}
    </div>
</x-layouts::app>
