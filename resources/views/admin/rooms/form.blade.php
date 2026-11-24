<x-layouts::app :title="$room->exists ? __('Edit Kamar') : __('Tambah Kamar')">
    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">Admin</p>
            <h1 class="mt-1 text-2xl font-black text-slate-900">{{ $room->exists ? 'Edit Kamar' : 'Tambah Kamar' }}</h1>
        </div>

        <form action="{{ $room->exists ? route('admin.rooms.update', $room) : route('admin.rooms.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            @if ($room->exists)
                @method('PUT')
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nama kamar</label>
                    <input id="name" name="name" value="{{ old('name', $room->name) }}" required maxlength="255" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <p class="mt-1 text-[11px] leading-4 text-slate-500">Contoh: Kamar Kipas Angin 01 atau Kamar AC 01.</p>
                    @error('name') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="slug" class="mb-1 block text-sm font-medium text-slate-700">Slug URL</label>
                    <input id="slug" name="slug" value="{{ old('slug', $room->slug) }}" required maxlength="255" pattern="[A-Za-z0-9_-]+" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    @error('slug') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="room_type" class="mb-1 block text-sm font-medium text-slate-700">Fasilitas kamar</label>
                    <select id="room_type" name="room_type" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                        @foreach (['fan' => 'Kipas angin', 'ac' => 'AC'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('room_type', $room->room_type ?: 'fan') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('room_type') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                    <p class="mt-1 text-[11px] leading-4 text-slate-500">Tarif mengikuti jenis kamar.</p>
                </div>
                <div>
                    <label for="capacity" class="mb-1 block text-sm font-medium text-slate-700">Kapasitas tamu</label>
                    <input id="capacity" type="number" name="capacity" value="{{ old('capacity', $room->capacity ?: 2) }}" required min="1" max="255" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    @error('capacity') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                    <select id="status" name="status" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                        @foreach (['available' => 'Tersedia', 'unavailable' => 'Tidak tersedia', 'maintenance' => 'Perawatan'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $room->status ?: 'available') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    @php
                        $imageUrl = $room->imageUrl();
                    @endphp
                    <label for="image" class="mb-1 block text-sm font-medium text-slate-700">Foto kamar</label>
                    <div class="relative">
                        <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" aria-label="Pilih foto kamar" class="peer absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0">
                        <div class="flex min-h-28 items-center gap-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3 transition peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-sky-600">
                        <img
                            id="image-preview"
                            src="{{ $imageUrl ?? '' }}"
                            alt="Pratinjau foto {{ $room->name }}"
                            @if (! $imageUrl) hidden @endif
                                class="h-24 w-36 rounded-lg object-cover"
                        >
                            <p id="image-preview-placeholder" @if ($imageUrl) hidden @endif class="text-xs text-slate-500">Klik untuk memilih foto kamar.</p>
                        </div>
                    </div>
                    <p class="mt-1 text-[11px] leading-4 text-slate-500">JPG, PNG, atau WEBP, maksimal 5 MB.</p>
                    @error('image') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                <textarea id="description" name="description" rows="3" required maxlength="5000" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">{{ old('description', $room->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $room->is_featured))>
                Tampilkan sebagai kamar unggulan
            </label>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Simpan kamar</button>
                <a href="{{ route('admin.rooms.index') }}" class="rounded-full border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>

    <script>
        const roomImageInput = document.getElementById('image');
        const roomImagePreview = document.getElementById('image-preview');
        const imagePreviewPlaceholder = document.getElementById('image-preview-placeholder');
        let previewUrl;

        roomImageInput.addEventListener('change', () => {
            const imageFile = roomImageInput.files[0];

            if (!imageFile || !imageFile.type.startsWith('image/')) {
                return;
            }

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }

            previewUrl = URL.createObjectURL(imageFile);
            roomImagePreview.src = previewUrl;
            roomImagePreview.hidden = false;
            imagePreviewPlaceholder.hidden = true;
        });

        window.addEventListener('pagehide', () => {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }
        });
    </script>
</x-layouts::app>
