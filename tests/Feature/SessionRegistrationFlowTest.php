<?php

namespace Tests\Feature;

use App\Enums\DrawingSessionStatus;
use App\Enums\SessionRegistrationStatus;
use App\Models\Category;
use App\Models\DrawingSession;
use App\Models\SessionRegistration;
use App\Models\TrainerProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_client_can_register_for_open_session(): void
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $trainer = User::factory()->create();
        $trainer->assignRole('trainer');

        $session = DrawingSession::factory()->create([
            'trainer_profile_id' => TrainerProfile::factory()->create(['user_id' => $trainer->id])->id,
            'category_id' => Category::factory()->create(['type' => 'session'])->id,
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(5)->addHours(2),
            'capacity' => 5,
            'registered_count' => 1,
            'status' => DrawingSessionStatus::Open,
        ]);

        $response = $this->actingAs($client)->post(route('account.registrations.store', $session->slug));

        $response->assertRedirect();

        $this->assertDatabaseHas('session_registrations', [
            'user_id' => $client->id,
            'drawing_session_id' => $session->id,
            'status' => SessionRegistrationStatus::Registered->value,
        ]);

        $this->assertSame(2, $session->fresh()->registered_count);
    }

    public function test_registration_marks_session_full_at_capacity(): void
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $trainer = User::factory()->create();
        $trainer->assignRole('trainer');

        $session = DrawingSession::factory()->create([
            'trainer_profile_id' => TrainerProfile::factory()->create(['user_id' => $trainer->id])->id,
            'category_id' => Category::factory()->create(['type' => 'session'])->id,
            'starts_at' => now()->addDays(3),
            'ends_at' => now()->addDays(3)->addHours(2),
            'capacity' => 2,
            'registered_count' => 1,
            'status' => DrawingSessionStatus::Open,
        ]);

        $this->actingAs($client)->post(route('account.registrations.store', $session->slug));

        $this->assertSame(2, $session->fresh()->registered_count);
        $this->assertSame(DrawingSessionStatus::Full, $session->fresh()->status);
    }

    public function test_client_can_cancel_registration_before_deadline(): void
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $trainer = User::factory()->create();
        $trainer->assignRole('trainer');

        $session = DrawingSession::factory()->create([
            'trainer_profile_id' => TrainerProfile::factory()->create(['user_id' => $trainer->id])->id,
            'category_id' => Category::factory()->create(['type' => 'session'])->id,
            'starts_at' => now()->addDays(4),
            'ends_at' => now()->addDays(4)->addHours(2),
            'capacity' => 3,
            'registered_count' => 3,
            'status' => DrawingSessionStatus::Full,
        ]);

        $registration = SessionRegistration::factory()->create([
            'user_id' => $client->id,
            'drawing_session_id' => $session->id,
            'status' => SessionRegistrationStatus::Registered,
        ]);

        $response = $this->actingAs($client)->post(route('account.registrations.cancel', $registration));

        $response->assertRedirect();
        $this->assertSame(2, $session->fresh()->registered_count);
        $this->assertSame(DrawingSessionStatus::Open, $session->fresh()->status);
        $this->assertSame(SessionRegistrationStatus::Cancelled, $registration->fresh()->status);
    }

    public function test_client_can_re_register_after_cancellation(): void
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $trainer = User::factory()->create();
        $trainer->assignRole('trainer');

        $session = DrawingSession::factory()->create([
            'trainer_profile_id' => TrainerProfile::factory()->create(['user_id' => $trainer->id])->id,
            'category_id' => Category::factory()->create(['type' => 'session'])->id,
            'starts_at' => now()->addDays(4),
            'ends_at' => now()->addDays(4)->addHours(2),
            'capacity' => 3,
            'registered_count' => 2,
            'status' => DrawingSessionStatus::Open,
        ]);

        $registration = SessionRegistration::factory()->create([
            'user_id' => $client->id,
            'drawing_session_id' => $session->id,
            'status' => SessionRegistrationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        $response = $this->actingAs($client)->post(route('account.registrations.store', $session->slug));

        $response->assertRedirect();
        $this->assertSame(3, $session->fresh()->registered_count);
        $this->assertSame(DrawingSessionStatus::Full, $session->fresh()->status);

        $freshRegistration = $registration->fresh();
        $this->assertSame(SessionRegistrationStatus::Registered, $freshRegistration->status);
        $this->assertNull($freshRegistration->cancelled_at);
    }
}
