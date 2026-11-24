<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_accounts_are_email_verified_when_seeded(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@penginapananisa')->firstOrFail();
        $this->assertTrue($admin->hasVerifiedEmail());
        $this->assertSame(UserRole::ADMIN, $admin->role);
        $this->assertDatabaseMissing('users', ['email' => 'admin@penginapananisa.test']);

        $this->post(route('login.store'), [
            'email' => 'admin@penginapananisa',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_open_user_management_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]));

        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.users.create'))->assertOk();
    }

    public function test_admin_can_create_edit_and_delete_a_user_without_reservation_history(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]));

        $this->post(route('admin.users.store'), [
            'name' => 'Staf Baru',
            'email' => 'staff-new@example.test',
            'phone' => '628123456789',
            'role' => UserRole::STAFF->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('email', 'staff-new@example.test')->firstOrFail();
        $this->assertSame(UserRole::STAFF, $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotNull($user->email_verified_at);

        $this->put(route('admin.users.update', $user), [
            'name' => 'Staf Diperbarui',
            'email' => 'staff-new@example.test',
            'phone' => '628987654321',
            'role' => UserRole::GUEST->value,
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Staf Diperbarui',
            'role' => UserRole::GUEST->value,
        ]);

        $this->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_with_reservation_history_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $guest = User::factory()->create(['role' => UserRole::GUEST]);
        $room = Room::create([
            'name' => 'Kamar Tes',
            'slug' => 'kamar-tes-user-crud',
            'description' => 'Kamar untuk tes.',
            'price_per_night' => 300000,
            'capacity' => 2,
            'status' => 'available',
        ]);
        Reservation::create([
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'check_in' => '2026-11-01',
            'check_out' => '2026-11-02',
            'nights' => 1,
            'total_amount' => 300000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $guest))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $guest->id]);
    }

    public function test_guest_cannot_access_user_management(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::GUEST]));

        $this->get(route('admin.users.index'))->assertForbidden();
    }
}
