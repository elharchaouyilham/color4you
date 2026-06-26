<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    /**
     * Create a new reservation with multiple products.
     *
     * @param User $user
     * @param array $items Array of ['product_id' => int, 'quantity' => int]
     * @return Reservation
     * @throws ValidationException
     */
    public function create(User $user, array $items): Reservation
    {
        return DB::transaction(function () use ($user, $items) {
            $attachData = [];

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity = (int) $item['quantity'];

                $product = Product::query()->lockForUpdate()->findOrFail($productId);

                if ($quantity > $product->availableQuantity()) {
                    throw ValidationException::withMessages([
                        'quantity' => "Requested quantity for product '{$product->name}' exceeds available stock.",
                    ]);
                }

                $attachData[$productId] = ['quantity' => $quantity];
            }

            $reservation = Reservation::query()->create([
                'user_id' => $user->id,
                'reserved_at' => now(),
                'status' => ReservationStatus::Pending,
            ]);

            $reservation->products()->attach($attachData);

            return $reservation;
        });
    }

    public function cancel(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation) {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if (!$reservation->canCancel()) {
                throw ValidationException::withMessages([
                    'reservation' => 'This reservation can no longer be cancelled.',
                ]);
            }

            if ($reservation->status === ReservationStatus::Confirmed) {
                foreach ($reservation->products()->lockForUpdate()->get() as $product) {
                    $qty = $product->pivot->quantity;
                    $product->reserved_quantity = max(0, $product->reserved_quantity - $qty);
                    $product->save();
                }
            }

            $reservation->status = ReservationStatus::Cancelled;
            $reservation->cancelled_at = now();
            $reservation->save();

            return $reservation;
        });
    }

    public function confirm(Reservation $reservation, int $daysToPickUp = 7): Reservation
    {
        return DB::transaction(function () use ($reservation, $daysToPickUp) {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($reservation->status !== ReservationStatus::Pending) {
                throw ValidationException::withMessages([
                    'reservation' => 'Only pending reservations can be confirmed.',
                ]);
            }

            $productsToUpdate = [];
            foreach ($reservation->products()->lockForUpdate()->get() as $product) {
                $qty = $product->pivot->quantity;
                if ($qty > $product->availableQuantity()) {
                    throw ValidationException::withMessages([
                        'reservation' => "Insufficient stock to confirm '{$product->name}'.",
                    ]);
                }
                $productsToUpdate[] = ['product' => $product, 'quantity' => $qty];
            }

            foreach ($productsToUpdate as $item) {
                $product = $item['product'];
                $product->reserved_quantity += $item['quantity'];
                $product->save();
            }

            $reservation->status = ReservationStatus::Confirmed;
            $reservation->pickup_due_at = now()->addDays($daysToPickUp);
            $reservation->save();

            return $reservation;
        });
    }

    public function reject(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation) {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($reservation->status !== ReservationStatus::Pending) {
                throw ValidationException::withMessages([
                    'reservation' => 'Only pending reservations can be rejected.',
                ]);
            }

            $reservation->status = ReservationStatus::Rejected;
            $reservation->save();

            return $reservation;
        });
    }

    public function pickup(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation) {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($reservation->status !== ReservationStatus::Confirmed) {
                throw ValidationException::withMessages([
                    'reservation' => 'Only confirmed reservations can be picked up.',
                ]);
            }

            foreach ($reservation->products()->lockForUpdate()->get() as $product) {
                $qty = $product->pivot->quantity;
                $product->stock_quantity = max(0, $product->stock_quantity - $qty);
                $product->reserved_quantity = max(0, $product->reserved_quantity - $qty);
                $product->save();
            }

            $reservation->status = ReservationStatus::PickedUp;
            $reservation->picked_up_at = now();
            $reservation->save();

            return $reservation;
        });
    }

    public function returnProduct(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation) {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($reservation->status !== ReservationStatus::PickedUp) {
                throw ValidationException::withMessages([
                    'reservation' => 'Only picked up reservations can be returned.',
                ]);
            }

            foreach ($reservation->products()->lockForUpdate()->get() as $product) {
                $qty = $product->pivot->quantity;
                $product->stock_quantity += $qty;
                $product->save();
            }

            $reservation->status = ReservationStatus::Returned;
            $reservation->returned_at = now();
            $reservation->save();

            return $reservation;
        });
    }
}
