<?php

namespace Tests\Feature;

use App\Enums\ContactStatus;
use App\Enums\DrawingSessionStatus;
use App\Enums\ProductStatus;
use App\Enums\ReservationStatus;
use App\Enums\SessionRegistrationStatus;
use App\Models\Category;
use App\Models\Contact;
use App\Models\DrawingSession;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\SessionRegistration;
use App\Models\TrainerProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTrainerExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $trainer;
    protected User $client;
    protected TrainerProfile $trainerProfile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->trainer = User::factory()->create();
        $this->trainer->assignRole('trainer');
        $this->trainerProfile = TrainerProfile::factory()->create([
            'user_id' => $this->trainer->id,
            'is_active' => true,
        ]);

        $this->client = User::factory()->create();
        $this->client->assignRole('client');
    }

    public function test_dashboard_route_redirects_correctly_based_on_role(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertRedirect(route('admin.dashboard'));

        $response = $this->actingAs($this->trainer)->get(route('dashboard'));
        $response->assertRedirect(route('trainer.dashboard'));

        $response = $this->actingAs($this->client)->get(route('dashboard'));
        $response->assertRedirect(route('account.dashboard'));
    }

    public function test_route_restrictions_are_enforced(): void
    {
        // Client trying to access trainer or admin
        $this->actingAs($this->client)->get(route('trainer.dashboard'))->assertStatus(403);
        $this->actingAs($this->client)->get(route('admin.dashboard'))->assertStatus(403);

        // Trainer trying to access client or admin
        $this->actingAs($this->trainer)->get(route('account.dashboard'))->assertStatus(403);
        $this->actingAs($this->trainer)->get(route('admin.dashboard'))->assertStatus(403);

        // Admin trying to access client or trainer
        $this->actingAs($this->admin)->get(route('account.dashboard'))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('trainer.dashboard'))->assertStatus(403);
    }

    public function test_trainer_can_view_assigned_sessions(): void
    {
        $category = Category::factory()->create(['type' => 'session']);
        $session = DrawingSession::factory()->create([
            'trainer_profile_id' => $this->trainerProfile->id,
            'category_id' => $category->id,
            'status' => DrawingSessionStatus::PendingTrainer,
        ]);

        $response = $this->actingAs($this->trainer)->get(route('trainer.dashboard'));
        $response->assertStatus(200);
        $response->assertSee($session->title);
    }

    public function test_trainer_can_confirm_or_refuse_assigned_session(): void
    {
        $category = Category::factory()->create(['type' => 'session']);
        
        // Test confirm
        $session1 = DrawingSession::factory()->create([
            'trainer_profile_id' => $this->trainerProfile->id,
            'category_id' => $category->id,
            'status' => DrawingSessionStatus::PendingTrainer,
        ]);
        $response = $this->actingAs($this->trainer)->post(route('trainer.sessions.respond', $session1), [
            'response' => 'confirm',
            'note' => 'I will be there.',
        ]);
        $response->assertRedirect();
        $this->assertEquals(DrawingSessionStatus::Open, $session1->fresh()->status);
        $this->assertEquals('I will be there.', $session1->fresh()->trainer_response_note);

        // Test refuse
        $session2 = DrawingSession::factory()->create([
            'trainer_profile_id' => $this->trainerProfile->id,
            'category_id' => $category->id,
            'status' => DrawingSessionStatus::PendingTrainer,
        ]);
        $response = $this->actingAs($this->trainer)->post(route('trainer.sessions.respond', $session2), [
            'response' => 'refuse',
            'note' => 'I am unavailable.',
        ]);
        $response->assertRedirect();
        $this->assertEquals(DrawingSessionStatus::TrainerRefused, $session2->fresh()->status);
        $this->assertEquals('I am unavailable.', $session2->fresh()->trainer_response_note);
    }

    public function test_trainer_can_fetch_participants_list(): void
    {
        $category = Category::factory()->create(['type' => 'session']);
        $session = DrawingSession::factory()->create([
            'trainer_profile_id' => $this->trainerProfile->id,
            'category_id' => $category->id,
            'status' => DrawingSessionStatus::Open,
        ]);

        $reg = SessionRegistration::factory()->create([
            'user_id' => $this->client->id,
            'drawing_session_id' => $session->id,
            'status' => SessionRegistrationStatus::Registered,
        ]);

        $response = $this->actingAs($this->trainer)->get(route('trainer.sessions.participants', $session));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $reg->id,
            'status' => 'registered',
            'email' => $this->client->email,
        ]);
    }

    public function test_trainer_can_mark_attendance(): void
    {
        $category = Category::factory()->create(['type' => 'session']);
        $session = DrawingSession::factory()->create([
            'trainer_profile_id' => $this->trainerProfile->id,
            'category_id' => $category->id,
            'status' => DrawingSessionStatus::Open,
        ]);

        $reg = SessionRegistration::factory()->create([
            'user_id' => $this->client->id,
            'drawing_session_id' => $session->id,
            'status' => SessionRegistrationStatus::Registered,
        ]);

        $response = $this->actingAs($this->trainer)->post(route('trainer.registrations.attendance', $reg), [
            'status' => 'attended',
        ]);
        $response->assertStatus(200);
        $this->assertEquals(SessionRegistrationStatus::Attended, $reg->fresh()->status);
    }

    public function test_admin_can_view_dashboard_stats(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    public function test_admin_category_crud(): void
    {
        // Store
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Paint Brushes',
            'description' => 'Various paint brushes',
            'type' => 'product',
            'is_active' => true,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Paint Brushes', 'type' => 'product']);

        $category = Category::where('name', 'Paint Brushes')->first();

        // Index
        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));
        $response->assertStatus(200);

        // Update
        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name' => 'Premium Paint Brushes',
            'type' => 'product',
            'is_active' => false,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Premium Paint Brushes', 'is_active' => false]);

        // Destroy
        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category->fresh()));
        $response->assertRedirect();
        $this->assertSoftDeleted($category->fresh());
    }

    public function test_admin_product_crud(): void
    {
        $cat = Category::factory()->create(['type' => 'product']);

        // Store
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'category_id' => $cat->id,
            'reference' => 'PROD-101',
            'name' => 'Easel Stand',
            'description' => 'Wooden easel stand',
            'price' => 45.00,
            'stock_quantity' => 10,
            'status' => 'available',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['reference' => 'PROD-101', 'name' => 'Easel Stand']);

        $product = Product::where('reference', 'PROD-101')->first();

        // Index
        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));
        $response->assertStatus(200);

        // Update
        $response = $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'category_id' => $cat->id,
            'reference' => 'PROD-101',
            'name' => 'Premium Easel Stand',
            'price' => 50.00,
            'stock_quantity' => 8,
            'status' => 'available',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Premium Easel Stand', 'stock_quantity' => 8]);

        // Destroy
        $response = $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product->fresh()));
        $response->assertRedirect();
        $this->assertSoftDeleted($product->fresh());
    }

    public function test_admin_reservation_transitions(): void
    {
        $cat = Category::factory()->create(['type' => 'product']);
        $product = Product::factory()->create([
            'category_id' => $cat->id,
            'stock_quantity' => 10,
            'reserved_quantity' => 0,
            'status' => ProductStatus::Available,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'status' => ReservationStatus::Pending,
        ]);
        $reservation->products()->attach($product->id, ['quantity' => 3]);

        // Confirm
        $response = $this->actingAs($this->admin)->post(route('admin.reservations.confirm', $reservation));
        $response->assertRedirect();
        $this->assertEquals(ReservationStatus::Confirmed, $reservation->fresh()->status);
        $this->assertEquals(3, $product->fresh()->reserved_quantity);

        // Pickup
        $response = $this->actingAs($this->admin)->post(route('admin.reservations.pickup', $reservation));
        $response->assertRedirect();
        $this->assertEquals(ReservationStatus::PickedUp, $reservation->fresh()->status);
        // physical stock decreases from 10 to 7. reserved quantity decreases from 3 to 0.
        $this->assertEquals(7, $product->fresh()->stock_quantity);
        $this->assertEquals(0, $product->fresh()->reserved_quantity);

        // Return
        $response = $this->actingAs($this->admin)->post(route('admin.reservations.return', $reservation));
        $response->assertRedirect();
        $this->assertEquals(ReservationStatus::Returned, $reservation->fresh()->status);
        // physical stock increases back to 10
        $this->assertEquals(10, $product->fresh()->stock_quantity);
    }

    public function test_admin_session_crud(): void
    {
        $cat = Category::factory()->create(['type' => 'session']);

        // Store
        $response = $this->actingAs($this->admin)->post(route('admin.sessions.store'), [
            'trainer_profile_id' => $this->trainerProfile->id,
            'category_id' => $cat->id,
            'title' => 'Watercolor for Beginners',
            'description' => 'Intro class',
            'starts_at' => now()->addDays(5)->toDateTimeString(),
            'ends_at' => now()->addDays(5)->addHours(2)->toDateTimeString(),
            'capacity' => 15,
            'price' => 20.00,
            'status' => 'draft',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('drawing_sessions', ['title' => 'Watercolor for Beginners', 'capacity' => 15]);

        $session = DrawingSession::where('title', 'Watercolor for Beginners')->first();

        // Index
        $response = $this->actingAs($this->admin)->get(route('admin.sessions.index'));
        $response->assertStatus(200);

        // Update
        $response = $this->actingAs($this->admin)->put(route('admin.sessions.update', $session), [
            'trainer_profile_id' => $this->trainerProfile->id,
            'category_id' => $cat->id,
            'title' => 'Advanced Watercolor',
            'starts_at' => now()->addDays(6)->toDateTimeString(),
            'ends_at' => now()->addDays(6)->addHours(3)->toDateTimeString(),
            'capacity' => 12,
            'price' => 25.00,
            'status' => 'open',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('drawing_sessions', ['title' => 'Advanced Watercolor', 'capacity' => 12]);

        // Destroy
        $response = $this->actingAs($this->admin)->delete(route('admin.sessions.destroy', $session->fresh()));
        $response->assertRedirect();
        $this->assertSoftDeleted($session->fresh());
    }

    public function test_admin_contact_resolution(): void
    {
        $contact = Contact::factory()->create([
            'status' => ContactStatus::New,
        ]);

        // Index marks New as Read
        $response = $this->actingAs($this->admin)->get(route('admin.contacts.index'));
        $response->assertStatus(200);
        $this->assertEquals(ContactStatus::Read, $contact->fresh()->status);

        // Resolve marks Read as Closed
        $response = $this->actingAs($this->admin)->post(route('admin.contacts.resolve', $contact));
        $response->assertRedirect();
        $this->assertEquals(ContactStatus::Closed, $contact->fresh()->status);
    }
}
