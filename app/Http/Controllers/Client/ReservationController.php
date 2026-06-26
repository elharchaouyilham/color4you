<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest;
use App\Models\Product;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;

class ReservationController extends Controller
{
    public function store(
        ReservationRequest $request,
        Product $product,
        ReservationService $reservationService,
    ): RedirectResponse {
        $items = [
            [
                'product_id' => $product->id,
                'quantity' => (int) $request->validated('quantity'),
            ],
        ];

        $reservationService->create($request->user(), $items);

        return back()->with('success', 'Reservation request submitted.');
    }

    public function cancel(
        Reservation $reservation,
        ReservationService $reservationService,
    ): RedirectResponse {
        $this->authorize('cancel', $reservation);

        $reservationService->cancel($reservation);

        return back()->with('success', 'Reservation cancelled.');
    }
}
