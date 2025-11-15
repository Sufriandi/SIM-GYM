<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;

// Controller Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IzinLatihanController as AdminIzinLatihanController;
// use App\Http\Controllers\Admin\MemberController;
// use App\Http\Controllers\Admin\ProdukController;

// Controller Member
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\IzinLatihanController as MemberIzinLatihanController;

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
| Rute /dashboard bawaan Breeze, dimodifikasi untuk mengarahkan
| berdasarkan 'role' setelah user login.
*/
Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->role === 'admin') {
        // Akan mengarah ke rute 'admin.dashboard' di Grup 4
        return redirect()->route('admin.dashboard');
    }
    
    // Asumsikan default adalah member
    // Akan mengarah ke rute 'member.dashboard' di Grup 5
    return redirect()->route('member.dashboard');
    
})->middleware(['auth', 'verified'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| 3. RUTE PROFIL UMUM (Bawaan Breeze)
|--------------------------------------------------------------------------
| Dibutuhkan untuk halaman 'Edit Profil'
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
| Semua rute yang hanya bisa diakses oleh Admin.
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard (Nama rute: admin.dashboard)
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Admin: Izin Latihan
    Route::get('izin-latihan', [AdminIzinLatihanController::class, 'index'])->name('izin_latihan.index');
    Route::get('izin-latihan/{id}/detail', [AdminIzinLatihanController::class, 'detail'])->name('izin_latihan.detail');
    Route::post('izin-latihan/{id}/approve', [AdminIzinLatihanController::class, 'approve'])->name('izin_latihan.approve');
    Route::post('izin-latihan/{id}/reject', [AdminIzinLatihanController::class, 'reject'])->name('izin_latihan.reject');
    
    // Tambahkan Rute Admin lainnya di sini:
    // Route::resource('members', MemberController::class);
    // Route::resource('produk', ProdukController::class);
});


/*
|--------------------------------------------------------------------------
| 5. RUTE KHUSUS MEMBER
|--------------------------------------------------------------------------
| Semua rute yang hanya bisa diakses oleh Member.
*/
Route::middleware(['auth', 'member'])->prefix('member')->name('member.')->group(function () {
    
    // Dashboard (Nama rute: member.dashboard)
    Route::get('dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');

    // Member: Izin Latihan
    Route::get('izin-latihan/form', [MemberIzinLatihanController::class, 'index'])->name('izin_latihan.index');
    Route::get('izin-latihan/riwayat', [MemberIzinLatihanController::class, 'riwayat'])->name('izin_latihan.riwayat');
    Route::post('izin-latihan/submit', [MemberIzinLatihanController::class, 'submit'])->name('izin_latihan.submit');
});


/*
|--------------------------------------------------------------------------
| 6. RUTE AUTENTIKASI (Bawaan Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';