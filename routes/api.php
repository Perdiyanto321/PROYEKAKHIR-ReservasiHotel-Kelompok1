<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok', 'service' => 'Svara Hotel API v1']));

    // Anggota Tim 1 (feat/api-hotel) akan menambahkan endpoint login, rooms, & reservations di bawah baris ini
});
