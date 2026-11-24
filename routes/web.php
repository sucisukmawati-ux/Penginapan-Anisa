<?php

use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\ReservationManagementController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\GuestRoomController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::post('/testimoni', [TestimonialController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('testimonials.store');
Route::post('/midtrans/notifications', [PaymentController::class, 'notification'])
    ->name('midtrans.notifications');
Route::get('/kamar', [GuestRoomController::class, 'index'])->name('rooms.index');
Route::get('/kamar/{room:slug}', [GuestRoomController::class, 'show'])->name('rooms.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::middleware('role:admin')->prefix('/admin')->name('admin.')->group(function () {
        Route::resource('pengguna', UserController::class)->except('show')->parameters(['pengguna' => 'user'])->names('users');
    });
    Route::get('/admin/reservasi', [AdminController::class, 'reservations'])->middleware('role:staff')->name('admin.reservations');
    Route::patch('/admin/reservasi/{reservation}/status', [ReservationManagementController::class, 'updateStatus'])
        ->middleware('role:staff')
        ->name('admin.reservations.update-status');
    Route::middleware('role:admin')->prefix('/admin')->name('admin.')->group(function () {
        Route::resource('kamar', RoomController::class)->except('show')->parameters(['kamar' => 'room'])->names('rooms');
        Route::resource('fasilitas', FacilityController::class)->except('show')->parameters(['fasilitas' => 'facility'])->names('facilities');
    });

    Route::get('/reservasi', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservasi/create/{room}', [ReservationController::class, 'create'])->name('reservations.create');
    Route::get('/reservasi/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservasi', [ReservationController::class, 'store'])->name('reservations.store');
    Route::post('/reservasi/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::post('/reservasi/{reservation}/checkout', [PaymentController::class, 'checkout'])->name('reservations.checkout');
});

require __DIR__.'/settings.php';
