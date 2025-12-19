<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\SesiAbsensiController;
use App\Http\Controllers\Api\KehadiranMemberController;
use App\Http\Controllers\Api\CoachController;
use App\Http\Controllers\Api\IzinLatihanController;
use App\Http\Controllers\Api\MembershipController;


// =======================================================
// RUTE PUBLIK (Prefix otomatis: /api/...)
// =======================================================

// AUTH (LOGIN & REGISTER)
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// PRODUK (PUBLIC) — DIPAKE ANDROID
Route::get('produk', [ProdukController::class, 'index']);    
Route::get('produk/{id}', [ProdukController::class, 'show']);  

// Sesi Absensi
Route::get('/sesi', [SesiAbsensiController::class, 'index']);
Route::post('/sesi', [SesiAbsensiController::class, 'store']);
Route::get('/sesi/{id}', [SesiAbsensiController::class, 'show']);
Route::post('/sesi/{id}/close', [SesiAbsensiController::class, 'closeSesi']);
Route::delete('/sesi/{id}', [SesiAbsensiController::class, 'destroy']);

// Absensi Member (Scan QR)
Route::post('/absen/scan', [KehadiranMemberController::class, 'scan']);
Route::get('/absen/member/{id}', [KehadiranMemberController::class, 'historyMember']);
Route::get('/absen/sesi/{id}', [KehadiranMemberController::class, 'listBySesi']);

// Coach
Route::get('/coaches', [CoachController::class, 'index']);

// Izin Latihan
Route::get('/izin-latihan', [IzinLatihanController::class, 'index']);
Route::post('/izin-latihan', [IzinLatihanController::class, 'store']);
Route::put('/izin-latihan/{id}/status', [IzinLatihanController::class, 'updateStatus']);
Route::get('/izin-latihan/member/{member}', [IzinLatihanController::class, 'historyByMember']);

// Membership
Route::get('/membership/dashboard/{member}', [MembershipController::class, 'dashboard']);
Route::get('/membership/history/{member}', [MembershipController::class, 'historyByMember']);


// =======================================================
// RUTE PRIVAT (Memerlukan Token Sanctum)
// =======================================================
Route::middleware('auth:sanctum')->group(function () {

    // Mengambil data user yang sedang login
    Route::get('user', function (Request $request) {
        return $request->user();
    });

});
