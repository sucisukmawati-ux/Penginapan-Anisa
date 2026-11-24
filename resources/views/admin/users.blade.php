<x-layouts::app :title="__('Kelola Pengguna')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-2xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-600">Users</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900">Kelola Pengguna</h1>
            </div>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Tambah pengguna</a>
        </div>

        @if (session('success'))
            <div role="status" class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if ($errors->has('user'))
            <div role="alert" class="rounded-xl bg-rose-50 p-4 text-sm text-rose-800">{{ $errors->first('user') }}</div>
        @endif

        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nama</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Role</th>
                            <th class="px-4 py-3 font-medium">No. HP</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ ucfirst($user->role->value) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $user->phone ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="font-semibold text-sky-700 hover:underline">Edit</a>
                                        @if (! $user->is(auth()->user()) && $user->reservations_count === 0)
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus pengguna ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-semibold text-rose-700 hover:underline">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">Belum ada pengguna.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{ $users->links() }}
    </div>
</x-layouts::app>
