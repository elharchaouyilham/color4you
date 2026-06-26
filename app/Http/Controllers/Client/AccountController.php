<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $user->load([
            'reservations.products.category:id,name,slug',
            'sessionRegistrations.drawingSession.category:id,name,slug',
        ]);

        $reservations = $user->reservations()
            ->with('products.category:id,name,slug')
            ->latest()
            ->get()
            ->map(fn ($reservation): array => [
                'id' => $reservation->id,
                'status' => $reservation->status->value,
                'reserved_at' => $reservation->reserved_at?->toIso8601String(),
                'pickup_due_at' => $reservation->pickup_due_at?->toIso8601String(),
                'cancelled_at' => $reservation->cancelled_at?->toIso8601String(),
                'can_cancel' => $reservation->canCancel(),
                'products' => $reservation->products->map(fn ($product): array => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'quantity' => $product->pivot->quantity,
                    'category_name' => $product->category?->name,
                ])->toArray(),
            ]);

        $registrations = $user->sessionRegistrations()
            ->with('drawingSession.category:id,name,slug')
            ->latest()
            ->get()
            ->map(fn ($registration): array => [
                'id' => $registration->id,
                'status' => $registration->status->value,
                'registered_at' => $registration->registered_at?->toIso8601String(),
                'cancelled_at' => $registration->cancelled_at?->toIso8601String(),
                'can_cancel' => $registration->canCancel(),
                'session' => [
                    'title' => $registration->drawingSession?->title,
                    'slug' => $registration->drawingSession?->slug,
                    'starts_at' => $registration->drawingSession?->starts_at?->toIso8601String(),
                    'category_name' => $registration->drawingSession?->category?->name,
                ],
            ]);

        return Inertia::render('Client/Dashboard', [
            'reservations' => $reservations,
            'registrations' => $registrations,
        ]);
    }
}
