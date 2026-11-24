<x-layouts::app :title="$facility->exists ? __('Edit Fasilitas') : __('Tambah Fasilitas')">
    <div class="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-6">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-600">Admin</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">{{ $facility->exists ? 'Edit Fasilitas' : 'Tambah Fasilitas' }}</h1>
        </div>

        <form action="{{ $facility->exists ? route('admin.facilities.update', $facility) : route('admin.facilities.store') }}" method="POST" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @if ($facility->exists)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nama fasilitas</label>
                <input id="name" name="name" value="{{ old('name', $facility->name) }}" required maxlength="255" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                @error('name') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="icon" class="mb-1 block text-sm font-medium text-slate-700">Nama ikon (opsional)</label>
                <input id="icon" name="icon" value="{{ old('icon', $facility->icon) }}" maxlength="100" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                @error('icon') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Deskripsi (opsional)</label>
                <textarea id="description" name="description" rows="3" maxlength="1000" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">{{ old('description', $facility->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Simpan fasilitas</button>
                <a href="{{ route('admin.facilities.index') }}" class="rounded-full border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
</x-layouts::app>
