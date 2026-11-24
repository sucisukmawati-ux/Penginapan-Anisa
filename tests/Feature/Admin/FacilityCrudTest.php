<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Facility;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_facility_management_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]));

        $this->get(route('admin.facilities.index'))->assertOk();
        $this->get(route('admin.facilities.create'))->assertOk();
    }

    public function test_admin_can_create_update_and_delete_a_facility(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]));

        $this->post(route('admin.facilities.store'), [
            'name' => 'Sarapan',
            'icon' => 'coffee',
            'description' => 'Sarapan tersedia setiap pagi.',
        ])->assertRedirect(route('admin.facilities.index'));

        $facility = Facility::query()->where('name', 'Sarapan')->firstOrFail();
        $this->assertDatabaseHas('facilities', ['id' => $facility->id, 'icon' => 'coffee']);

        $this->put(route('admin.facilities.update', $facility), [
            'name' => 'Sarapan Pagi',
            'icon' => 'sun',
            'description' => 'Menu sarapan hangat.',
        ])->assertRedirect(route('admin.facilities.index'));

        $this->assertDatabaseHas('facilities', ['id' => $facility->id, 'name' => 'Sarapan Pagi']);

        $room = Room::create([
            'name' => 'Kamar Uji',
            'slug' => 'kamar-uji',
            'description' => 'Untuk tes relasi fasilitas.',
            'price_per_night' => 350000,
            'capacity' => 2,
            'status' => 'available',
        ]);
        $room->facilities()->attach($facility);

        $this->delete(route('admin.facilities.destroy', $facility))
            ->assertRedirect(route('admin.facilities.index'));

        $this->assertDatabaseMissing('facilities', ['id' => $facility->id]);
        $this->assertDatabaseMissing('facility_room', [
            'room_id' => $room->id,
            'facility_id' => $facility->id,
        ]);
    }

    public function test_non_admin_cannot_access_facility_management(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::GUEST]));

        $this->get(route('admin.facilities.index'))->assertForbidden();
    }
}
