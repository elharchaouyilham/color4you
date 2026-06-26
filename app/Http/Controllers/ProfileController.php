<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;

class ProfileController extends Controller
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

    public function show()
    {
        $this->checkStatus();
        return view('profile.show');
    }

    public function update(Request $request)
    {
        $this->checkStatus();
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'nullable|string',
        ]);

        Auth::user()->update($request->only('nom', 'prenom', 'telephone'));
        return back()->with('success', 'Profil mis à jour.');
    }

    public function clientDashboard()
    {
        $this->checkStatus();
        if (!Auth::user()->hasRole('Client')) {
            abort(403, 'Accès réservé aux clients.');
        }

        $reservations = Reservation::where('user_id', Auth::id())->with('products')->get();
        $inscriptions = Auth::user()->inscriptions;
        return view('client.dashboard', compact('reservations', 'inscriptions'));
    }
}