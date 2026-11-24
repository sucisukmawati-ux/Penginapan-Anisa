<x-layouts::app :title="$user->exists ? __('Edit Pengguna') : __('Tambah Pengguna')">
    <div class="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-6">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-600">Admin</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">{{ $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h1>
        </div>

        <form action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @if ($user->exists)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                @error('name') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                @error('email') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Nomor WhatsApp (opsional)</label>
                <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                @error('phone') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="role" class="mb-1 block text-sm font-medium text-slate-700">Role</label>
                <select id="role" name="role" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm">
                    @foreach (\App\Enums\UserRole::cases() as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $user->role?->value ?? 'guest') === $role->value)>{{ ucfirst($role->value) }}</option>
                    @endforeach
                </select>
                @error('role') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">{{ $user->exists ? 'Password baru (opsional)' : 'Password' }}</label>
                <input id="password" type="password" name="password" @required(! $user->exists) minlength="8" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                @error('password') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Konfirmasi password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" @required(! $user->exists) minlength="8" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Simpan pengguna</button>
                <a href="{{ route('admin.users.index') }}" class="rounded-full border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
</x-layouts::app>
