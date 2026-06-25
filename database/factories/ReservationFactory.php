<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reserved_at' => now(),
            'pickup_due_at' => now()->addDays(3),
            'picked_up_at' => null,
            'returned_at' => null,
            'cancelled_at' => null,
            'status' => ReservationStatus::Pending,
        ];
    }
}
