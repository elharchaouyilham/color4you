<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DrawingSession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DrawingSessionController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $categorySlug = $request->string('category')->toString();

        $sessions = DrawingSession::query()
            ->with(['trainerProfile.user:id,first_name,last_name', 'category:id,name,slug'])
            ->upcoming()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($categorySlug !== '', function ($query) use ($categorySlug) {
                $query->whereHas('category', fn ($builder) => $builder->where('slug', $categorySlug));
            })
            ->whereIn('status', [
                \App\Enums\DrawingSessionStatus::Open,
                \App\Enums\DrawingSessionStatus::Full,
            ])
            ->orderBy('starts_at')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (DrawingSession $session): array => [
                'id' => $session->id,
                'title' => $session->title,
                'slug' => $session->slug,
                'description' => $session->description,
                'starts_at' => $session->starts_at?->toIso8601String(),
                'ends_at' => $session->ends_at?->toIso8601String(),
                'capacity' => $session->capacity,
                'registered_count' => $session->registered_count,
                'available_seats' => $session->availableSeats(),
                'price' => $session->price,
                'status' => $session->status->value,
                'trainer_name' => $session->trainerProfile?->user?->fullName(),
                'category' => $session->category?->only(['id', 'name', 'slug']),
                'image_url' => $session->getFirstMediaUrl('cover', 'thumb') ?: null,
            ]);

        $categories = Category::query()
            ->active()
            ->sessionCategories()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Sessions/Index', [
            'filters' => [
                'search' => $search,
                'category' => $categorySlug,
            ],
            'categories' => $categories,
            'sessions' => $sessions,
        ]);
    }

    public function show(DrawingSession $drawingSession): Response
    {
        $drawingSession->load(['trainerProfile.user:id,first_name,last_name', 'category:id,name,slug']);

        $relatedSessions = DrawingSession::query()
            ->with('trainerProfile.user:id,first_name,last_name')
            ->upcoming()
            ->whereKeyNot($drawingSession->id)
            ->where('category_id', $drawingSession->category_id)
            ->whereIn('status', [
                \App\Enums\DrawingSessionStatus::Open,
                \App\Enums\DrawingSessionStatus::Full,
            ])
            ->orderBy('starts_at')
            ->limit(3)
            ->get()
            ->map(fn (DrawingSession $session): array => [
                'id' => $session->id,
                'title' => $session->title,
                'slug' => $session->slug,
                'starts_at' => $session->starts_at?->toIso8601String(),
                'available_seats' => $session->availableSeats(),
                'trainer_name' => $session->trainerProfile?->user?->fullName(),
            ]);

        return Inertia::render('Sessions/Show', [
            'session' => [
                'id' => $drawingSession->id,
                'title' => $drawingSession->title,
                'slug' => $drawingSession->slug,
                'description' => $drawingSession->description,
                'starts_at' => $drawingSession->starts_at?->toIso8601String(),
                'ends_at' => $drawingSession->ends_at?->toIso8601String(),
                'capacity' => $drawingSession->capacity,
                'registered_count' => $drawingSession->registered_count,
                'available_seats' => $drawingSession->availableSeats(),
                'price' => $drawingSession->price,
                'status' => $drawingSession->status->value,
                'trainer_name' => $drawingSession->trainerProfile?->user?->fullName(),
                'trainer_specialty' => $drawingSession->trainerProfile?->specialty,
                'category' => $drawingSession->category?->only(['id', 'name', 'slug']),
                'image_url' => $drawingSession->getFirstMediaUrl('cover') ?: null,
            ],
            'relatedSessions' => $relatedSessions,
        ]);
    }
}
