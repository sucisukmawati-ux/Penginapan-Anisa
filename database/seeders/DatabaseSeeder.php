<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Facility;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = 'admin@penginapananisa';
        $legacyAdminEmail = 'admin@penginapananisa.test';

        if (! User::query()->where('email', $adminEmail)->exists()) {
            User::query()->where('email', $legacyAdminEmail)->update(['email' => $adminEmail]);
        }

        $demoUsers = [
            [
                'email' => $adminEmail,
                'name' => 'Admin Penginapan',
                'role' => UserRole::ADMIN,
                'phone' => '628123456789',
            ],
            [
                'email' => 'staff@penginapananisa',
                'name' => 'Staf Resepsionis',
                'role' => UserRole::STAFF,
                'phone' => '628987654321',
            ],
            [
                'email' => 'tamu@penginapananisa',
                'name' => 'Tamu Pengguna',
                'role' => UserRole::GUEST,
                'phone' => '628112233445',
            ],
        ];

        foreach ($demoUsers as $demoUser) {
            $user = User::query()->firstOrCreate(
                ['email' => $demoUser['email']],
                [
                    'name' => $demoUser['name'],
                    'password' => Hash::make('password123'),
                    'role' => $demoUser['role'],
                    'phone' => $demoUser['phone'],
                    'email_verified_at' => now(),
                ],
            );

            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }
        }

        $fanFacility = Facility::query()->firstOrCreate(
            ['name' => 'Kipas angin'],
            ['icon' => 'fan', 'description' => 'Kamar dengan fasilitas kipas angin.'],
        );
        $acFacility = Facility::query()->firstOrCreate(
            ['name' => 'AC'],
            ['icon' => 'wind', 'description' => 'Kamar dengan fasilitas air conditioner (AC).'],
        );
        $legacySlugs = ['deluxe-twin', 'family-suite', 'garden-view'];

        for ($number = 1; $number <= 28; $number++) {
            $roomType = $number <= 6 ? Room::TYPE_AC : Room::TYPE_FAN;
            $roomNumber = $roomType === Room::TYPE_AC ? $number : $number - 6;
            $slug = sprintf('kamar-%02d', $number);
            $room = Room::query()->where('slug', $slug)->first();

            if (! $room && isset($legacySlugs[$number - 1])) {
                $room = Room::query()->where('slug', $legacySlugs[$number - 1])->first();
            }

            $room ??= new Room;
            $room->fill([
                'name' => sprintf($roomType === Room::TYPE_AC ? 'Kamar AC %02d' : 'Kamar Kipas Angin %02d', $roomNumber),
                'slug' => $slug,
                'description' => 'Kamar berukuran sama dengan fasilitas '.($roomType === Room::TYPE_AC ? 'AC' : 'kipas angin').'.',
                'room_type' => $roomType,
                'price_per_night' => Room::RATES[$roomType]['overnight'],
                'capacity' => 2,
                'status' => 'available',
                'image_url' => $roomType === Room::TYPE_AC
                    ? 'images/kamarAC'.$roomNumber.'.jpg'
                    : 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80',
            ]);
            $room->save();
            $room->facilities()->sync([$roomType === Room::TYPE_AC ? $acFacility->id : $fanFacility->id]);
        }
    }
}
