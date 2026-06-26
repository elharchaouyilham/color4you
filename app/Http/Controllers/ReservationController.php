<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    private function checkStatus()
    {
        if (Auth::user()->status === 'banni') {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            abort(403, 'Compte suspendu.');
        }
    }

    public function store(Request $request)
    {
        $this->checkStatus();
        if (!Auth::user()->hasRole('Client')) abort(403);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'date_reservation' => 'required|date|after_or_equal:today',
        ]);

        try {
            DB::beginTransaction();
            $product = Product::lockForUpdate()->find($request->product_id);

            if ($product->quantite < 1) {
                DB::rollBack();
                return back()->with('error', 'Stock épuisé.');
            }

            $product->decrement('quantite', 1);

            $reservation = Reservation::create([
                'date_reservation' => $request->date_reservation,
                'status' => 'En attente',
                'user_id' => Auth::id(),
            ]);

            $reservation->products()->attach($product->id, ['quantite' => 1]);

            DB::commit();
            return back()->with('success', 'Demande de réservation envoyée.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur transactionnelle.');
        }
    }

    public function confirm(Reservation $reservation)
    {
        if (!Auth::user()->hasRole('Administrateur')) abort(403);
        $reservation->update(['status' => 'Confirmée']);
        return back()->with('success', 'Matériel donné au client.');
    }

    public function complete(Reservation $reservation)
    {
        if (!Auth::user()->hasRole('Administrateur')) abort(403);
        try {
            DB::beginTransaction();
            $reservation->update(['status' => 'Terminée', 'date_retour' => now()]);

            foreach ($reservation->products as $product) {
                $product->increment('quantite', $product->pivot->quantite);
            }

            DB::commit();
            return back()->with('success', 'Retour validé, stock réapprovisionné.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur.');
        }
    }
}