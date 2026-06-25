<?php

namespace Database\Factories;

use App\Enums\SessionRegistrationStatus;
use App\Models\DrawingSession;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionRegistration>
 */
class SessionRegistrationFactory extends Factory
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
            'drawing_session_id' => DrawingSession::factory()->open(),
            'registered_at' => now(),
            'cancelled_at' => null,
            'status' => SessionRegistrationStatus::Registered,
        ];
    }
}
