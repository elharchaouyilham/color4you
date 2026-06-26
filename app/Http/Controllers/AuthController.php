<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            if (Auth::user()->status === 'banni') {
                Auth::logout();
                return back()->withErrors(['email' => 'Votre compte a été banni.']);
            }
            $request->session()->regenerate();
            return redirect()->route('catalogue');
        }
        return back()->withErrors(['email' => 'Identifiants incorrects.']);
    }

    public function showRegister() { return view('auth.register'); }

    public function register(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'status' => 'actif'
        ]);

        $user->assignRole('Client');
        Auth::login($user);
        return redirect()->route('catalogue');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('catalogue');
    }
}