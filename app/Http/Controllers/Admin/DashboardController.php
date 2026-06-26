<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\DrawingSession;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $stats = [
            'total_products' => Product::count(),
            'total_reservations' => Reservation::count(),
            'pending_reservations' => Reservation::query()->where('status', 'pending')->count(),
            'total_sessions' => DrawingSession::count(),
            'upcoming_sessions' => DrawingSession::query()->where('starts_at', '>', now())->count(),
            'total_trainers' => User::trainers()->count(),
            'new_contacts' => Contact::query()->where('status', 'new')->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }
}
