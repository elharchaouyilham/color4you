<?php

namespace App\Http\Controllers;

use App\Models\DrawingSession;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        if (!Schema::hasTable('products') || !Schema::hasTable('drawing_sessions')) {
            return Inertia::render('Home', [
                'featuredProducts' => [],
                'upcomingSessions' => [],
            ]);
        }

        $featuredProducts = Product::query()
            ->with('category:id,name,slug')
            ->available()
            ->recent(6)
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'reference' => $product->reference,
                'price' => $product->price,
                'available_quantity' => $product->availableQuantity(),
                'category' => $product->category?->only(['id', 'name', 'slug']),
                'image_url' => $product->getFirstMediaUrl('images', 'thumb') ?: null,
            ]);

        $upcomingSessions = DrawingSession::query()
            ->with(['trainerProfile.user:id,first_name,last_name', 'category:id,name,slug'])
            ->upcoming()
            ->open()
            ->orderBy('starts_at')
            ->limit(4)
            ->get()
            ->map(fn (DrawingSession $session): array => [
                'id' => $session->id,
                'title' => $session->title,
                'slug' => $session->slug,
                'starts_at' => $session->starts_at?->toIso8601String(),
                'ends_at' => $session->ends_at?->toIso8601String(),
                'available_seats' => $session->availableSeats(),
                'price' => $session->price,
                'category' => $session->category?->only(['id', 'name', 'slug']),
                'trainer_name' => $session->trainerProfile?->user?->fullName(),
                'image_url' => $session->getFirstMediaUrl('cover', 'thumb') ?: null,
            ]);

        return Inertia::render('Home', [
            'featuredProducts' => $featuredProducts,
            'upcomingSessions' => $upcomingSessions,
        ]);
    }
}
