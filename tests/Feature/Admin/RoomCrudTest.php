<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoomCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_room_management_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]));

        $this->get(route('admin.rooms.index'))->assertOk();
        $this->get(route('admin.rooms.create'))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('type="file" name="image"', false)
            ->assertSee('id="image-preview"', false)
            ->assertSee('Klik untuk memilih foto kamar.')
            ->assertDontSee('name="facility_ids[]"', false);
    }

    public function test_admin_can_create_update_and_delete_a_room(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]));
        Storage::fake('public');
        $facility = Facility::create(['name' => 'Wi-Fi']);

        $this->post(route('admin.rooms.store'), [
            'name' => 'Kamar Baru',
            'slug' => 'kamar-baru',
            'description' => 'Kamar nyaman untuk dua orang.',
            'room_type' => 'fan',
            'capacity' => 2,
            'status' => 'available',
            'facility_ids' => [$facility->id],
            'image' => UploadedFile::fake()->image('room.jpg'),
        ])->assertRedirect(route('admin.rooms.index'));

        $room = Room::query()->where('slug', 'kamar-baru')->firstOrFail();
        $originalImagePath = $room->image_url;
        Storage::disk('public')->assertExists($originalImagePath);
        $this->assertDatabaseHas('facility_room', [
            'room_id' => $room->id,
            'facility_id' => $facility->id,
        ]);
        $this->assertSame(250000.0, (float) $room->price_per_night);
        $this->get(route('rooms.index'))
            ->assertOk()
            ->assertSee($room->imageUrl(), false);
        $this->get(route('rooms.show', $room))
            ->assertOk()
            ->assertSee($room->imageUrl(), false);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee($room->imageUrl(), false);

        $this->put(route('admin.rooms.update', $room), [
            'name' => 'Kamar Diperbarui',
            'slug' => 'kamar-diperbarui',
            'description' => 'Deskripsi baru.',
            'room_type' => 'ac',
            'capacity' => 3,
            'status' => 'maintenance',
            'image' => UploadedFile::fake()->image('updated-room.jpg'),
        ])->assertRedirect(route('admin.rooms.index'));

        $updatedImagePath = $room->fresh()->image_url;
        Storage::disk('public')->assertExists($updatedImagePath);
        Storage::disk('public')->assertMissing($originalImagePath);
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Kamar Diperbarui',
            'room_type' => 'ac',
            'price_per_night' => 350000,
            'status' => 'maintenance',
        ]);
        $this->assertDatabaseHas('facility_room', [
            'room_id' => $room->id,
            'facility_id' => $facility->id,
        ]);

        $this->delete(route('admin.rooms.destroy', $room))
            ->assertRedirect(route('admin.rooms.index'));

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
        Storage::disk('public')->assertMissing($updatedImagePath);
    }

    public function test_admin_sees_a_clear_message_when_a_room_slug_is_already_used(): void
    {
        Room::create([
            'name' => 'Kamar yang sudah ada',
            'slug' => 'kamar-sudah-ada',
            'description' => 'Kamar lama.',
            'room_type' => Room::TYPE_FAN,
            'price_per_night' => Room::RATES[Room::TYPE_FAN]['overnight'],
            'capacity' => 2,
            'status' => 'available',
        ]);

        $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]))
            ->from(route('admin.rooms.create'))
            ->post(route('admin.rooms.store'), [
                'name' => 'Kamar baru',
                'slug' => 'kamar-sudah-ada',
                'description' => 'Kamar baru.',
                'room_type' => Room::TYPE_FAN,
                'capacity' => 2,
                'status' => 'available',
            ])
            ->assertRedirect(route('admin.rooms.create'))
            ->assertSessionHasErrors([
                'slug' => 'Slug URL sudah digunakan. Silakan gunakan slug lain.',
            ]);
    }

    public function test_admin_cannot_delete_a_room_with_reservation_history(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $guest = User::factory()->create(['role' => UserRole::GUEST]);
        $room = Room::create([
            'name' => 'Kamar Bersejarah',
            'slug' => 'kamar-bersejarah',
            'description' => 'Kamar dengan reservasi lama.',
            'price_per_night' => 400000,
            'capacity' => 2,
            'status' => 'available',
        ]);
        Reservation::create([
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'check_in' => '2026-11-01',
            'check_out' => '2026-11-02',
            'nights' => 1,
            'total_amount' => 400000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.rooms.index'))
            ->assertOk()
            ->assertSee('Hapus kamar tidak tersedia karena memiliki riwayat reservasi', false)
            ->assertSee('disabled', false);

        $this->actingAs($admin)
            ->from(route('admin.rooms.index'))
            ->delete(route('admin.rooms.destroy', $room))
            ->assertRedirect(route('admin.rooms.index'))
            ->assertSessionHasErrors('room');

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_non_admin_cannot_access_room_management(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::STAFF]));

        $this->get(route('admin.rooms.index'))->assertForbidden();
    }
}
