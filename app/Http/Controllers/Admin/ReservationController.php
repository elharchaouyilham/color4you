<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function index(): Response
    {
        $reservations = Reservation::query()
            ->with(['user', 'products'])
            ->latest('reserved_at')
            ->get()
            ->map(fn ($reservation): array => [
                'id' => $reservation->id,
                'user' => [
                    'id' => $reservation->user?->id,
                    'name' => $reservation->user?->name,
                    'email' => $reservation->user?->email,
                    'phone' => $reservation->user?->phone,
                ],
                'products' => $reservation->products->map(fn ($product): array => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'reference' => $product->reference,
                    'quantity' => $product->pivot->quantity,
                    'available_quantity' => $product->availableQuantity(),
                ])->toArray(),
                'status' => $reservation->status->value,
                'reserved_at' => $reservation->reserved_at?->toIso8601String(),
                'pickup_due_at' => $reservation->pickup_due_at?->toIso8601String(),
                'picked_up_at' => $reservation->picked_up_at?->toIso8601String(),
                'returned_at' => $reservation->returned_at?->toIso8601String(),
                'cancelled_at' => $reservation->cancelled_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/Reservations/Index', [
            'reservations' => $reservations,
        ]);
    }

    public function confirm(Reservation $reservation, ReservationService $reservationService): RedirectResponse
    {
        $reservationService->confirm($reservation);
        return back()->with('success', 'Reservation confirmed successfully.');
    }

    public function reject(Reservation $reservation, ReservationService $reservationService): RedirectResponse
    {
        $reservationService->reject($reservation);
        return back()->with('success', 'Reservation rejected successfully.');
    }

    public function pickup(Reservation $reservation, ReservationService $reservationService): RedirectResponse
    {
        $reservationService->pickup($reservation);
        return back()->with('success', 'Product marked as picked up.');
    }

    public function return(Reservation $reservation, ReservationService $reservationService): RedirectResponse
    {
        $reservationService->returnProduct($reservation);
        return back()->with('success', 'Product marked as returned.');
    }
}
