<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::query()
            ->withCount('reservations')
            ->latest()
            ->paginate(15);

        return view('admin.rooms.index', compact('rooms'));
    }

    public function create(): View
    {
        $room = new Room;

        return view('admin.rooms.form', compact('room'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $facilityIds = $validated['facility_ids'] ?? [];
        unset($validated['facility_ids'], $validated['image']);
        $validated['image_url'] = $this->storeRoomImage($request);

        $room = Room::create($validated);
        $room->facilities()->sync($facilityIds);

        return redirect()->route('admin.rooms.index')->with('success', 'Kamar berhasil ditambahkan.');
    }

    public function edit(Room $room): View
    {
        return view('admin.rooms.form', compact('room'));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $validated = $this->validatedData($request, $room);
        $facilityIds = $validated['facility_ids'] ?? null;
        unset($validated['facility_ids'], $validated['image']);
        $newImagePath = $this->storeRoomImage($request);
        $oldImagePath = $room->image_url;

        if ($newImagePath !== null) {
            $validated['image_url'] = $newImagePath;
        }

        $room->update($validated);

        if ($facilityIds !== null) {
            $room->facilities()->sync($facilityIds);
        }

        if ($newImagePath !== null) {
            $this->deleteStoredRoomImage($oldImagePath);
        }

        return redirect()->route('admin.rooms.index')->with('success', 'Data kamar berhasil diperbarui.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        if ($room->reservations()->exists()) {
            return redirect()->route('admin.rooms.index')
                ->withErrors(['room' => 'Kamar memiliki riwayat reservasi sehingga tidak dapat dihapus. Ubah statusnya menjadi tidak tersedia.']);
        }

        $imagePath = $room->image_url;
        $room->delete();
        $this->deleteStoredRoomImage($imagePath);

        return redirect()->route('admin.rooms.index')->with('success', 'Kamar berhasil dihapus.');
    }

    /**
     * @return array{name: string, slug: string, description: string, room_type: string, price_per_night: int, capacity: int, status: string, image?: \Illuminate\Http\UploadedFile, is_featured: bool, facility_ids: array<int, int>}
     */
    private function validatedData(Request $request, ?Room $room = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('rooms', 'slug')->ignore($room)],
            'description' => ['required', 'string', 'max:5000'],
            'room_type' => ['required', Rule::in(Room::ROOM_TYPES)],
            'capacity' => ['required', 'integer', 'min:1', 'max:255'],
            'status' => ['required', Rule::in(['available', 'unavailable', 'maintenance'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_featured' => ['sometimes', 'boolean'],
            'facility_ids' => ['sometimes', 'array'],
            'facility_ids.*' => ['integer', 'distinct', 'exists:facilities,id'],
        ], [
            'slug.unique' => 'Slug URL sudah digunakan. Silakan gunakan slug lain.',
        ]) + [
            'price_per_night' => Room::RATES[$request->input('room_type')]['overnight'],
            'is_featured' => $request->boolean('is_featured'),
        ];
    }

    private function storeRoomImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->store('rooms', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('Gambar kamar gagal disimpan.');
        }

        return $path;
    }

    private function deleteStoredRoomImage(?string $path): void
    {
        if (! is_string($path) || ! str_starts_with($path, 'rooms/')) {
            return;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($path) && ! $disk->delete($path)) {
            throw new RuntimeException('Gambar kamar lama gagal dihapus.');
        }
    }
}
