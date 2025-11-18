<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;

// Controller Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IzinLatihanController;
use App\Http\Controllers\Admin\PenjualanProdukController; 
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\CoachController;
// use App\Http\Controllers\Admin\MemberController;

// Controller Member
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\IzinLatihanController as MemberIzinLatihanController;
use App\Http\Controllers\Member\MemberProfileController;

/*
|--------------------------------------------------------------------------
| 1. RUTE PUBLIK / GUEST
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
// Tambahkan rute publik lainnya di sini (misal: /tentang-kami)


/*
|--------------------------------------------------------------------------
| 2. PENGALIH DASHBOARD (REDIRECTOR)
|--------------------------------------------------------------------------
*/
Route::get(uri: '/dashboard', action: function () {
    if (!Auth::check()) {
        return redirect()->route(route: 'login');
    }

    if (Auth::user()->role === 'admin') {
        return redirect()->route(route: 'admin.dashboard');
    }

    // Asumsikan default adalah member
    return redirect()->route(route: 'member.dashboard');

})->middleware(['auth', 'verified'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| 3. RUTE PROFIL UMUM (Bawaan Breeze)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| 4. RUTE KHUSUS ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard (Nama rute: admin.dashboard)
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Izin Latihan Group (IzinLatihanController)
    Route::prefix('izin-latihan')->name('izin_latihan.')->group(function () {
        Route::get('/', [IzinLatihanController::class, 'index'])->name('index');
        Route::get('/riwayat', [IzinLatihanController::class, 'history'])->name('history');
        Route::get('/{id}/detail', [IzinLatihanController::class, 'show'])->name('detail');
        Route::get('/{izinLatihan}/setujui', [IzinLatihanController::class, 'approveForm'])->name('approve.form');
        Route::post('/{izinLatihan}/approve', [IzinLatihanController::class, 'approveIzin'])->name('approve');
        Route::post('/{id}/reject', [IzinLatihanController::class, 'reject'])->name('reject');
    });

    // =========================================================
    // RUTE MANAJEMEN PRODUK
    // =========================================================
    
    // 1. Rute Penjualan Produk (Hanya index, create, store, show, destroy)
    Route::resource('penjualan_produk', PenjualanProdukController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);
    
    // 2. RUTE Master Data Produk (CRUD Penuh)
    Route::resource('produk', ProdukController::class);

    // Rute lain untuk manajemen Stok Produk akan ditambahkan di sini
    // Route::resource('stok-produk', StokProdukController::class);
  
    // =========================================================
    // RUTE MANAJEMEN COACH
    // =========================================================
    Route::resource('coaches',CoachController::class);

});


/*
|--------------------------------------------------------------------------
| 5. RUTE KHUSUS MEMBER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'member'])->prefix('member')->name('member.')->group(function () {

    // Dashboard (Nama rute: member.dashboard)
    Route::get('dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');

    // Izin Latihan Group (MemberIzinLatihanController)
    Route::prefix('izin-latihan')->name('izin_latihan.')->group(function () {
        // 🚨 PERBAIKAN: Gunakan alias yang benar di sini
        Route::get('/', [MemberIzinLatihanController::class, 'index'])->name('index');
        Route::get('/riwayat', [MemberIzinLatihanController::class, 'history'])->name('history');
        Route::get('/ajukan', [MemberIzinLatihanController::class, 'create'])->name('create');
        Route::post('/store', [MemberIzinLatihanController::class, 'store'])->name('store');
    });

    // // Rute Pelengkapan Profil
    // Route::get('/profile/lengkapi', [MemberProfileController::class, 'showCompletionForm'])->name('profile.complete.show');
    // Route::post('/profile/lengkapi', [MemberProfileController::class, 'completeProfile'])->name('profile.complete.store');

});


/*
|--------------------------------------------------------------------------
| 6. RUTE AUTENTIKASI (Bawaan Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';