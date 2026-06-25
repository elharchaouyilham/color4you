<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\DrawingSession;
use App\Models\Reservation;
use App\Models\SessionRegistration;
use App\Enums\ReservationStatus;
use App\Enums\SessionRegistrationStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientAndBookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create client user
        $client = User::query()->updateOrCreate(
            ['email' => 'client@example.com'],
            [
                'first_name' => 'Bobby',
                'last_name' => 'Client',
                'phone' => '555123456',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $client->assignRole('client');

        // 2. Mock reservation
        $product = Product::query()->first();
        if ($product) {
            $quantity = 1;

            $existingReservation = Reservation::query()
                ->where('user_id', $client->id)
                ->whereHas('products', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->exists();

            if (!$existingReservation) {
                $reservation = Reservation::query()->create([
                    'user_id' => $client->id,
                    'reserved_at' => now(),
                    'pickup_due_at' => now()->addDays(3),
                    'status' => ReservationStatus::Confirmed,
                ]);

                $reservation->products()->attach($product->id, ['quantity' => $quantity]);

                $product->increment('reserved_quantity', $quantity);
            }
        }

        // 3. Mock drawing session registration
        $session = DrawingSession::query()->first();
        if ($session) {
            $existingRegistration = SessionRegistration::query()
                ->where('user_id', $client->id)
                ->where('drawing_session_id', $session->id)
                ->exists();

            if (!$existingRegistration) {
                SessionRegistration::query()->create([
                    'user_id' => $client->id,
                    'drawing_session_id' => $session->id,
                    'registered_at' => now(),
                    'status' => SessionRegistrationStatus::Registered,
                ]);

                $session->increment('registered_count');
            }
        }
    }
}
