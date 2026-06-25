<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Enums\ReservationStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_client_can_create_pending_reservation(): void
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $product = Product::factory()->create([
            'category_id' => Category::factory()->create(['type' => 'product'])->id,
            'stock_quantity' => 8,
            'reserved_quantity' => 2,
            'status' => ProductStatus::Available,
        ]);

        $response = $this->actingAs($client)->post(route('account.reservations.store', $product->slug), [
            'quantity' => 3,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('reservations', [
            'user_id' => $client->id,
            'status' => ReservationStatus::Pending->value,
        ]);

        $reservation = Reservation::where('user_id', $client->id)->first();
        $this->assertDatabaseHas('product_reservation', [
            'reservation_id' => $reservation->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertSame(2, $product->fresh()->reserved_quantity);
    }

    public function test_client_cannot_reserve_more_than_available_stock(): void
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $product = Product::factory()->create([
            'category_id' => Category::factory()->create(['type' => 'product'])->id,
            'stock_quantity' => 4,
            'reserved_quantity' => 3,
            'status' => ProductStatus::Available,
        ]);

        $response = $this->actingAs($client)->post(route('account.reservations.store', $product->slug), [
            'quantity' => 2,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('reservations', 0);
        $this->assertDatabaseCount('product_reservation', 0);
    }

    public function test_cancelling_confirmed_reservation_releases_reserved_stock(): void
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $product = Product::factory()->create([
            'category_id' => Category::factory()->create(['type' => 'product'])->id,
            'stock_quantity' => 10,
            'reserved_quantity' => 4,
            'status' => ProductStatus::Available,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'status' => ReservationStatus::Confirmed,
        ]);
        $reservation->products()->attach($product->id, ['quantity' => 2]);

        $response = $this->actingAs($client)->post(route('account.reservations.cancel', $reservation));

        $response->assertRedirect();
        $this->assertSame(2, $product->fresh()->reserved_quantity);
        $this->assertSame(ReservationStatus::Cancelled, $reservation->fresh()->status);
        $this->assertNotNull($reservation->fresh()->cancelled_at);
    }
}
