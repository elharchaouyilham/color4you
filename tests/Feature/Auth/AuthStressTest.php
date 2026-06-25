<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthStressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed Spatie roles for the tests
        Role::create(['name' => 'client', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'trainer', 'guard_name' => 'web']);
    }

    /**
     * Test registration with empty/missing first_name or last_name.
     */
    public function test_registration_fails_with_missing_names(): void
    {
        // 1. Missing first_name
        $response = $this->post('/register', [
            'first_name' => '',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors(['first_name']);

        // 2. Missing last_name
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => '',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors(['last_name']);
    }

    /**
     * Test phone field boundaries during registration.
     */
    public function test_registration_phone_field_boundaries(): void
    {
        // 1. Extremely long phone number (more than 255 characters)
        $longPhone = str_repeat('1', 256);
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'longphone@example.com',
            'phone' => $longPhone,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors(['phone']);

        // 2. Script injection in phone field
        $xssPhone = "<script>alert('xss')</script>";
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'xssphone@example.com',
            'phone' => $xssPhone,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasNoErrors();
        $user = User::where('email', 'xssphone@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame($xssPhone, $user->phone);
        $this->post('/logout');

        // 3. Unicode characters in phone field (e.g. Cyrillic, Emojis)
        $unicodePhone = "📞+1-555-𝟝𝟝𝟝-𝟘𝟙𝟡𝟚";
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'unicodephone@example.com',
            'phone' => $unicodePhone,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasNoErrors();
        $user = User::where('email', 'unicodephone@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame($unicodePhone, $user->phone);
        $this->post('/logout');

        // 4. Empty phone field (nullable check)
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'emptyphone@example.com',
            'phone' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasNoErrors();
        $user = User::where('email', 'emptyphone@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->phone);
    }

    /**
     * Validation that Spatie 'client' role is always assigned correctly upon registration,
     * and not assigned to administrative roles unless specified.
     */
    public function test_registration_always_assigns_client_role(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Role',
            'last_name' => 'Test',
            'email' => 'role@example.com',
            'phone' => '123456',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect();
        $user = User::where('email', 'role@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('client'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('trainer'));
    }

    /**
     * Profile update request corner cases.
     */
    public function test_profile_update_corner_cases(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // 1. Trying to change email to an already existing email of another user (should fail)
        $response = $this->actingAs($user1)->patch('/profile', [
            'first_name' => 'User',
            'last_name' => 'One',
            'email' => 'user2@example.com',
            'phone' => '1234567890',
        ]);
        $response->assertSessionHasErrors(['email']);

        // 2. Changing email to their own current email (should succeed)
        $response = $this->actingAs($user1)->patch('/profile', [
            'first_name' => 'User',
            'last_name' => 'One',
            'email' => 'user1@example.com',
            'phone' => '1234567890',
        ]);
        $response->assertSessionHasNoErrors();

        // 3. Trying to update with empty names (should fail)
        $response = $this->actingAs($user1)->patch('/profile', [
            'first_name' => '',
            'last_name' => '',
            'email' => 'user1@example.com',
            'phone' => '1234567890',
        ]);
        $response->assertSessionHasErrors(['first_name', 'last_name']);

        // 4. Nullable phone updates
        // First set to a phone
        $response = $this->actingAs($user1)->patch('/profile', [
            'first_name' => 'User',
            'last_name' => 'One',
            'email' => 'user1@example.com',
            'phone' => '987654321',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertSame('987654321', $user1->fresh()->phone);

        // Update to null/empty phone
        $response = $this->actingAs($user1)->patch('/profile', [
            'first_name' => 'User',
            'last_name' => 'One',
            'email' => 'user1@example.com',
            'phone' => '',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertNull($user1->fresh()->phone);
    }
}
