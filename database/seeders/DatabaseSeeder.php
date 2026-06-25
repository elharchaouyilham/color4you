<?php

namespace Database\Seeders;

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
        $this->call([
            RolePermissionSeeder::class,
            CategorySeeder::class,
            TrainerProfileSeeder::class,
            ProductSeeder::class,
            DrawingSessionSeeder::class,
            ClientAndBookingSeeder::class,
        ]);

        $admin = User::query()->firstOrCreate([
            'email' => env('ADMIN_EMAIL', 'admin@artt.test'),
        ], [
            'first_name' => env('ADMIN_FIRST_NAME', 'Admin'),
            'last_name' => env('ADMIN_LAST_NAME', 'User'),
            'phone' => env('ADMIN_PHONE'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            'email_verified_at' => now(),
        ]);

        $admin->syncRoles(['admin']);
    }
}
