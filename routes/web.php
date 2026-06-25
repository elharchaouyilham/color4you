<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\RegistrationController;
use App\Http\Controllers\Client\ReservationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DrawingSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\SessionController as AdminSessionController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;

use App\Http\Controllers\Trainer\DashboardController as TrainerDashboardController;
use App\Http\Controllers\Trainer\SessionResponseController as TrainerSessionResponseController;
use App\Http\Controllers\Trainer\ParticipantController as TrainerParticipantController;
use App\Http\Controllers\Trainer\AttendanceController as TrainerAttendanceController;

Route::get('/', HomeController::class)->name('home');
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{product:slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/sessions', [DrawingSessionController::class, 'index'])->name('sessions.index');
Route::get('/sessions/{drawingSession:slug}', [DrawingSessionController::class, 'show'])->name('sessions.show');
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:client'])->group(function () {
    Route::get('/account', AccountController::class)->name('account.dashboard');
    Route::post('/products/{product:slug}/reservations', [ReservationController::class, 'store'])
        ->name('account.reservations.store');
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])
        ->name('account.reservations.cancel');
    Route::post('/sessions/{drawingSession:slug}/registrations', [RegistrationController::class, 'store'])
        ->name('account.registrations.store');
    Route::post('/registrations/{sessionRegistration}/cancel', [RegistrationController::class, 'cancel'])
        ->name('account.registrations.cancel');
});

Route::middleware(['auth', 'verified', 'role:trainer'])->prefix('trainer')->name('trainer.')->group(function () {
    Route::get('/', TrainerDashboardController::class)->name('dashboard');
    Route::post('/sessions/{drawingSession}/respond', TrainerSessionResponseController::class)->name('sessions.respond');
    Route::get('/sessions/{drawingSession}/participants', TrainerParticipantController::class)->name('sessions.participants');
    Route::post('/registrations/{sessionRegistration}/attendance', TrainerAttendanceController::class)->name('registrations.attendance');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    
    // Categories CRUD
    Route::apiResource('categories', AdminCategoryController::class);
    
    // Products CRUD
    Route::apiResource('products', AdminProductController::class);
    Route::post('/products/{product}', [AdminProductController::class, 'update'])->name('products.update_post'); // Fallback for multipart/form-data PUT/PATCH
    
    // Reservations management
    Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
    Route::post('/reservations/{reservation}/confirm', [AdminReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations/{reservation}/reject', [AdminReservationController::class, 'reject'])->name('reservations.reject');
    Route::post('/reservations/{reservation}/pickup', [AdminReservationController::class, 'pickup'])->name('reservations.pickup');
    Route::post('/reservations/{reservation}/return', [AdminReservationController::class, 'return'])->name('reservations.return');

    // Drawing Sessions CRUD
    Route::apiResource('sessions', AdminSessionController::class);
    Route::post('/sessions/{session}', [AdminSessionController::class, 'update'])->name('sessions.update_post'); // Fallback for image upload updates

    // Visitor Messages / Contact resolve
    Route::get('/contacts', [AdminContactController::class, 'index'])->name('contacts.index');
    Route::post('/contacts/{contact}/resolve', [AdminContactController::class, 'resolve'])->name('contacts.resolve');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
