<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use Illuminate\Support\Facades\Route;
use App\Models\RoomType;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/explore', function () {
    $roomTypes = RoomType::with([
            'facilities',
            'rooms' => fn ($q) =>$q->tersedia()->orderBy('room_number'),
        ])
        ->withCount(['rooms' => fn ($q) =>$q->tersedia()])
        ->orderBy('base_price')
        ->get()
        ->each->append('cover_url');

    return Inertia::render('Explore', [
        'roomTypes' => $roomTypes,
    ]);
})->name('explore');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 1. Master Tipe Kamar (Hanya Admin via Gate)
    Route::resource('room-types', RoomTypeController::class)
        ->middleware('can:kelola-master-kamar');

    // 2. Unit Kamar: Tulis khusus Admin, Lihat untuk Admin & Resepsionis
    Route::resource('rooms', RoomController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->middleware('can:kelola-master-kamar');

    Route::resource('rooms', RoomController::class)
        ->only(['index', 'show'])
        ->middleware('can:operasional-frontdesk');

    // 3. Reservasi (Semua role terautentikasi, pembatasan objek via ReservationPolicy)
    Route::resource('reservations', ReservationController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);

    Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])
        ->name('reservations.status');

    Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])
        ->name('reservations.cancel');

});

require __DIR__.'/auth.php';