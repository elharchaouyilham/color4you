<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\DrawingSession;
use App\Models\SessionRegistration;
use App\Models\Reservation;
use App\Models\Contact;
use App\Models\TrainerProfile;
use App\Enums\ProductStatus;
use App\Enums\ReservationStatus;
use App\Enums\SessionRegistrationStatus;
use App\Enums\DrawingSessionStatus;
use App\Enums\ContactStatus;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseImplementerM2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    /** @test */
    public function it_verifies_user_roles_and_scopes()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $trainer = User::factory()->create();
        $trainer->assignRole('trainer');

        $client = User::factory()->create();
        $client->assignRole('client');

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isTrainer());
        $this->assertFalse($admin->isClient());

        $this->assertTrue($trainer->isTrainer());
        $this->assertTrue($client->isClient());

        $trainers = User::trainers()->get();
        $clients = User::clients()->get();

        $this->assertTrue($trainers->contains($trainer));
        $this->assertFalse($trainers->contains($client));
        $this->assertTrue($clients->contains($client));
        $this->assertFalse($clients->contains($trainer));
    }

    /** @test */
    public function it_verifies_category_relationships_and_scopes()
    {
        $activeProductCat = Category::factory()->create([
            'is_active' => true,
            'type' => 'product',
        ]);

        $inactiveProductCat = Category::factory()->create([
            'is_active' => false,
            'type' => 'product',
        ]);

        $sessionCat = Category::factory()->create([
            'is_active' => true,
            'type' => 'session',
        ]);

        $this->assertTrue(Category::active()->get()->contains($activeProductCat));
        $this->assertFalse(Category::active()->get()->contains($inactiveProductCat));

        $this->assertTrue(Category::productCategories()->get()->contains($activeProductCat));
        $this->assertFalse(Category::productCategories()->get()->contains($sessionCat));

        $this->assertTrue(Category::sessionCategories()->get()->contains($sessionCat));
        $this->assertFalse(Category::sessionCategories()->get()->contains($activeProductCat));
    }

    /** @test */
    public function it_verifies_product_scopes()
    {
        $cat = Category::factory()->create(['type' => 'product']);
        $subCat = Category::factory()->create(['type' => 'product', 'parent_id' => $cat->id]);

        $product1 = Product::factory()->create([
            'category_id' => $cat->id,
            'name' => 'Paint Brush Set',
            'reference' => 'REF-001',
            'description' => 'A fine set of brushes',
            'status' => ProductStatus::Available,
            'stock_quantity' => 10,
            'created_at' => now()->subDays(2),
        ]);

        $product2 = Product::factory()->create([
            'category_id' => $subCat->id,
            'name' => 'Canvas Board',
            'reference' => 'REF-002',
            'description' => 'For painting',
            'status' => ProductStatus::Available,
            'stock_quantity' => 5,
            'created_at' => now(),
        ]);

        $product3 = Product::factory()->create([
            'category_id' => $cat->id,
            'name' => 'Draft Pencil',
            'reference' => 'REF-003',
            'description' => 'Drafting pencil',
            'status' => ProductStatus::Draft,
            'stock_quantity' => 10,
            'created_at' => now()->subDays(5),
        ]);

        // Search scope
        $searchResults = Product::search('Brush')->get();
        $this->assertTrue($searchResults->contains($product1));
        $this->assertFalse($searchResults->contains($product2));

        // Available scope
        $availableProducts = Product::available()->get();
        $this->assertTrue($availableProducts->contains($product1));
        $this->assertTrue($availableProducts->contains($product2));
        $this->assertFalse($availableProducts->contains($product3));

        // Recent scope
        $recentProducts = Product::recent(1)->get();
        $this->assertCount(1, $recentProducts);
        $this->assertEquals($product2->id, $recentProducts->first()->id);

        // By Category scope (should include subcategory products)
        $byCatProducts = Product::byCategory($cat->id)->get();
        $this->assertTrue($byCatProducts->contains($product1));
        $this->assertTrue($byCatProducts->contains($product2)); // from subcategory
        $this->assertTrue($byCatProducts->contains($product3));
    }

    /** @test */
    public function it_verifies_drawing_session_relations_methods_and_scopes()
    {
        $trainerUser = User::factory()->create();
        $trainerUser->assignRole('trainer');
        $trainerProfile = TrainerProfile::factory()->create(['user_id' => $trainerUser->id]);

        $cat = Category::factory()->create(['type' => 'session']);

        $session1 = DrawingSession::factory()->create([
            'trainer_profile_id' => $trainerProfile->id,
            'category_id' => $cat->id,
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHours(2),
            'capacity' => 10,
            'registered_count' => 0,
            'status' => DrawingSessionStatus::Open,
        ]);

        $session2 = DrawingSession::factory()->create([
            'trainer_profile_id' => $trainerProfile->id,
            'category_id' => $cat->id,
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->subDays(1)->addHours(2),
            'capacity' => 5,
            'registered_count' => 5,
            'status' => DrawingSessionStatus::Full,
        ]);

        // Relations
        $this->assertEquals($cat->id, $session1->category->id);
        $this->assertTrue($cat->drawingSessions->contains($session1));

        // Scopes
        $upcoming = DrawingSession::upcoming()->get();
        $this->assertTrue($upcoming->contains($session1));
        $this->assertFalse($upcoming->contains($session2));

        $open = DrawingSession::open()->get();
        $this->assertTrue($open->contains($session1));
        $this->assertFalse($open->contains($session2));

        // Methods
        $this->assertFalse($session1->isFull());
        $this->assertTrue($session2->isFull());

        $clientUser = User::factory()->create();
        $clientUser->assignRole('client');

        $this->assertFalse($session1->hasUserRegistered($clientUser));

        SessionRegistration::factory()->create([
            'user_id' => $clientUser->id,
            'drawing_session_id' => $session1->id,
            'status' => SessionRegistrationStatus::Registered,
        ]);

        $this->assertTrue($session1->fresh()->hasUserRegistered($clientUser));
    }

    /** @test */
    public function it_verifies_session_registration_and_reservation_scopes_and_methods()
    {
        $session = DrawingSession::factory()->create([
            'starts_at' => now()->addHours(25),
        ]);

        $regActive = SessionRegistration::factory()->create([
            'drawing_session_id' => $session->id,
            'status' => SessionRegistrationStatus::Registered,
        ]);

        $regCancelled = SessionRegistration::factory()->create([
            'drawing_session_id' => $session->id,
            'status' => SessionRegistrationStatus::Cancelled,
        ]);

        $this->assertTrue(SessionRegistration::active()->get()->contains($regActive));
        $this->assertFalse(SessionRegistration::active()->get()->contains($regCancelled));

        $this->assertTrue($regActive->canCancel());
        $this->assertFalse($regCancelled->canCancel());

        // Test starts_at <= 24 hours
        $sessionShort = DrawingSession::factory()->create([
            'starts_at' => now()->addHours(23),
        ]);
        $regShort = SessionRegistration::factory()->create([
            'drawing_session_id' => $sessionShort->id,
            'status' => SessionRegistrationStatus::Registered,
        ]);
        $this->assertFalse($regShort->canCancel());

        // Test Reservation
        $resPending = Reservation::factory()->create([
            'status' => ReservationStatus::Pending,
            'pickup_due_at' => now()->addDays(2),
            'picked_up_at' => null,
        ]);

        $resPickedUp = Reservation::factory()->create([
            'status' => ReservationStatus::Confirmed,
            'pickup_due_at' => now()->addDays(2),
            'picked_up_at' => now(),
        ]);

        $resOverdue = Reservation::factory()->create([
            'status' => ReservationStatus::Confirmed,
            'pickup_due_at' => now()->subDays(1),
            'picked_up_at' => null,
        ]);

        $this->assertTrue(Reservation::active()->get()->contains($resPending));
        $this->assertTrue(Reservation::active()->get()->contains($resOverdue));
        $this->assertFalse(Reservation::active()->get()->contains($resPickedUp));

        $this->assertTrue(Reservation::overdue()->get()->contains($resOverdue));
        $this->assertFalse(Reservation::overdue()->get()->contains($resPending));

        $this->assertTrue($resPending->canCancel());
        $this->assertFalse($resPickedUp->canCancel());
    }

    /** @test */
    public function it_verifies_contact_scopes_and_methods()
    {
        $contactNew = Contact::factory()->create([
            'status' => ContactStatus::New,
            'read_at' => null,
        ]);

        $contactRead = Contact::factory()->create([
            'status' => ContactStatus::Read,
            'read_at' => now(),
        ]);

        $this->assertTrue(Contact::new()->get()->contains($contactNew));
        $this->assertFalse(Contact::new()->get()->contains($contactRead));

        $contactNew->markAsRead();
        $this->assertEquals(ContactStatus::Read, $contactNew->fresh()->status);
        $this->assertNotNull($contactNew->fresh()->read_at);
    }
}
