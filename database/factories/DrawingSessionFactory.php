<?php

namespace Database\Factories;

use App\Enums\DrawingSessionStatus;
use App\Models\DrawingSession;
use App\Models\TrainerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DrawingSession>
 */
class DrawingSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);
        $startsAt = fake()->dateTimeBetween('+1 day', '+2 months');
        $endsAt = (clone $startsAt)->modify('+2 hours');

        return [
            'trainer_profile_id' => TrainerProfile::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->optional()->paragraph(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'capacity' => fake()->numberBetween(5, 30),
            'registered_count' => 0,
            'price' => fake()->optional()->randomFloat(2, 0, 250),
            'status' => DrawingSessionStatus::Draft,
            'trainer_response_note' => null,
            'trainer_responded_at' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DrawingSessionStatus::Open,
        ]);
    }
}
