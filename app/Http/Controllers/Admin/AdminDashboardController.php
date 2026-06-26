<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    private function checkAdmin()
    {
        if (Auth::user()->status === 'banni' || !Auth::user()->hasRole('Administrateur')) {
            abort(403, 'Espace réservé aux Administrateurs.');
        }
    }

    public function index()
    {
        $this->checkAdmin();
        $totalProducts = Product::sum('quantite');
        $pendingReservationsCount = Reservation::where('status', 'En attente')->count();
        $activeUsersCount = User::where('status', 'actif')->count();
        $reservations = Reservation::with(['user', 'products'])->latest()->get();

        return view('admin.dashboard', compact('totalProducts', 'pendingReservationsCount', 'activeUsersCount', 'reservations'));
    }
}