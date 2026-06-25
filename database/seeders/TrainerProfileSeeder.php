<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TrainerProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TrainerProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainers = [
            [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane.doe@example.com',
                'phone' => '123456789',
                'specialty' => 'Aquarelle & Huile',
                'bio' => 'Artiste peintre professionnelle depuis 10 ans.',
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'phone' => '987654321',
                'specialty' => 'Fusain & Dessin académique',
                'bio' => 'Diplômé des Beaux-Arts, passionné par le dessin de portraits.',
            ],
        ];

        foreach ($trainers as $t) {
            $user = User::query()->updateOrCreate(
                ['email' => $t['email']],
                [
                    'first_name' => $t['first_name'],
                    'last_name' => $t['last_name'],
                    'phone' => $t['phone'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->assignRole('trainer');

            TrainerProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'specialty' => $t['specialty'],
                    'bio' => $t['bio'],
                    'is_active' => true,
                ]
            );
        }
    }
}
