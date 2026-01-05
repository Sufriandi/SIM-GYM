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
use App\Http\Controllers\Api\PaymentInfoController;
use App\Http\Controllers\Api\ProfilGymController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RiwayatPembelianProdukController;
use App\Http\Controllers\Api\AdminContactController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AdminNotificationController;
use App\Http\Controllers\Api\DeviceTokenController;

// =====================
// PUBLIC
// =====================
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Produk
Route::get('produk', [ProdukController::class, 'index']);
Route::get('produk/{id}', [ProdukController::class, 'show']);
Route::get('/transaksi-produk/member/{member}', [RiwayatPembelianProdukController::class, 'historyByMember']);
Route::get('/transaksi-produk/{id}', [RiwayatPembelianProdukController::class, 'show']);

// Sesi
Route::get('/sesi', [SesiAbsensiController::class, 'index']);
Route::post('/sesi', [SesiAbsensiController::class, 'store']);
Route::get('/sesi/{id}', [SesiAbsensiController::class, 'show']);
Route::post('/sesi/{id}/close', [SesiAbsensiController::class, 'closeSesi']);
Route::delete('/sesi/{id}', [SesiAbsensiController::class, 'destroy']);

// Absen Member
Route::post('/absen/scan', [KehadiranMemberController::class, 'scan']);
Route::get('/absen/member/{id}', [KehadiranMemberController::class, 'historyMember']);
Route::get('/absen/sesi/{id}', [KehadiranMemberController::class, 'listBySesi']);
Route::get('/kehadiran/member/{id}', [KehadiranMemberController::class, 'historyMember']);

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

// Pembayaran
Route::get('/payment-info', [PaymentInfoController::class, 'index']);

// Profil Gym
Route::get('/profil-gym', [ProfilGymController::class, 'show']);

// Admin Contact
Route::get('/admin-contact', [AdminContactController::class, 'show']);


// =====================
// PRIVATE (Sanctum)
// =====================
Route::middleware('auth:sanctum')->group(function () {

    Route::get('user', function (Request $request) {
        return $request->user();
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);

    // =====================
    // NOTIFIKASI (USER)
    // =====================
    // List notifikasi milik user (broadcast + personal)
    Route::get('/notifications', [NotificationController::class, 'index']);

    // Tandai 1 notifikasi sebagai sudah dibaca
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);

    // Tandai semua sebagai sudah dibaca
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Hapus 1 notifikasi (hapus untuk user ini saja / hide)
    Route::delete('/notifications/{id}', [NotificationController::class, 'deleteOne']);

    // =====================
    // NOTIFIKASI (ADMIN)
    // =====================
    // Admin membuat notifikasi (broadcast / personal)
    // NOTE: idealnya pakai middleware role admin, misal: ->middleware('is_admin')
    Route::post('/admin/notifications', [AdminNotificationController::class, 'store']);

    // Notifikasi Servies
    Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
});
