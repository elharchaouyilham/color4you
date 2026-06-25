<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthCornerCasesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed Spatie roles for the tests
        Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'trainer', 'guard_name' => 'web']);
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
            'email' => 'test_missing_fn@example.com',
            'phone' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors(['first_name']);

        // 2. Missing last_name
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => '',
            'email' => 'test_missing_ln@example.com',
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

        // 2. Script injection in phone field (should be accepted by validation and stored as-is without database error)
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

        // 3. Unicode characters in phone field (e.g. Cyrillic, Emojis, math unicode characters)
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
     * Also tests that sending roles/role input in the request cannot grant higher privileges.
     */
    public function test_registration_always_assigns_client_role_and_blocks_privilege_escalation(): void
    {
        // 1. Regular registration assigns 'client' and not admin/trainer
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
        $this->post('/logout');

        // 2. Register attempt with role/roles parameter (adversarial privilege escalation attempt)
        $response2 = $this->post('/register', [
            'first_name' => 'Hacker',
            'last_name' => 'User',
            'email' => 'hacker@example.com',
            'phone' => '123456',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
            'roles' => ['admin', 'trainer'],
        ]);

        $response2->assertRedirect();
        $user2 = User::where('email', 'hacker@example.com')->first();
        $this->assertNotNull($user2);
        // Should still only be 'client' and not 'admin' or 'trainer'
        $this->assertTrue($user2->hasRole('client'));
        $this->assertFalse($user2->hasRole('admin'));
        $this->assertFalse($user2->hasRole('trainer'));
    }

    /**
     * Profile update request corner cases.
     */
    public function test_profile_update_corner_cases(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // Set initial role for user1 to client
        $user1->assignRole('client');

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

        // 5. Try to escalate privilege via profile update request (sending role/roles parameter)
        $response = $this->actingAs($user1)->patch('/profile', [
            'first_name' => 'User',
            'last_name' => 'One',
            'email' => 'user1@example.com',
            'phone' => '987654321',
            'role' => 'admin',
            'roles' => ['admin'],
        ]);
        $response->assertSessionHasNoErrors();
        $user1Fresh = $user1->fresh();
        // Should not have admin role
        $this->assertFalse($user1Fresh->hasRole('admin'));
        $this->assertTrue($user1Fresh->hasRole('client'));
    }
}
