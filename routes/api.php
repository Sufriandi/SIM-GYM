<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProdukController;


// =======================================================
// RUTE PUBLIK (Prefix otomatis: /api/...)
// =======================================================

// AUTH (LOGIN & REGISTER)
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// PRODUK (PUBLIC) — DIPAKE ANDROID
Route::get('produk', [ProdukController::class, 'index']);    
Route::get('produk/{id}', [ProdukController::class, 'show']);  


// =======================================================
// RUTE PRIVAT (Memerlukan Token Sanctum)
// =======================================================
Route::middleware('auth:sanctum')->group(function () {

    // Mengambil data user yang sedang login
    Route::get('user', function (Request $request) {
        return $request->user();
    });

});
