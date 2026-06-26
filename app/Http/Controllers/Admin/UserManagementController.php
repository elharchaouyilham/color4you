<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function toggleStatus(User $user)
    {
        if (!Auth::user()->hasRole('Administrateur')) abort(403);
        if ($user->id === Auth::id()) return back()->with('error', 'Impossible de s’auto-bannir.');

        $user->update(['status' => $user->status === 'actif' ? 'banni' : 'actif']);
        return back()->with('success', 'Statut du compte utilisateur modifié.');
    }
}