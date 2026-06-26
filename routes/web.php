<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SeanceDessinController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ProductCrudController;
use App\Http\Controllers\Admin\CategoryCrudController;
use App\Http\Controllers\Admin\UserManagementController;

// --- SPHÈRE PUBLIQUE ---
Route::get('/', [CatalogueController::class, 'index'])->name('catalogue');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// --- SPHÈRE PRIVÉE (PROTECTION PAR L'AUTHENTIFICATION UNIQUE) ---
Route::middleware('auth')->group(function () {
    
    // Profil utilisateur commun
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Flux Client
    Route::get('/dashboard', [ProfileController::class, 'clientDashboard'])->name('client.dashboard');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::post('/seances/{seance}/inscrire', [SeanceDessinController::class, 'inscrire'])->name('seances.inscrire');

    // Flux Formateur
    Route::prefix('formateur')->name('formateur.')->group(function () {
        Route::get('/dashboard', [SeanceDessinController::class, 'formateurDashboard'])->name('dashboard');
        Route::post('/seances/{seance}/status', [SeanceDessinController::class, 'updateStatus'])->name('seances.status');
        Route::get('/seances/{seance}/export', [SeanceDessinController::class, 'exportEmargement'])->name('seances.export');
    });

    // Flux Administrateur
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/reservations/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
        Route::post('/reservations/{reservation}/complete', [ReservationController::class, 'complete'])->name('reservations.complete');
        Route::patch('/users/{user}/toggle', [UserManagementController::class, 'toggleStatus'])->name('users.toggle');

        Route::resource('categories', CategoryCrudController::class)->except(['show', 'edit', 'update']);
        Route::resource('products', ProductCrudController::class)->except(['show']);
    });
});