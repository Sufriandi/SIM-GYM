<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;

// Controller Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IzinLatihanController;
use App\Http\Controllers\Admin\InventarisAlatController;
use App\Http\Controllers\Admin\PenjualanProdukController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\StokProdukController;
use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\MemberController;

// Controller Membership (Admin)
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\PaketMembershipController;
use App\Http\Controllers\Admin\MembershipGroupController;
use App\Http\Controllers\Admin\ProfilGymController;

// Controller Absensi (Admin)
use App\Http\Controllers\Admin\KehadiranMemberController as AdminKehadiranMemberController;

// Controller Member
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\IzinLatihanController as MemberIzinLatihanController;
use App\Http\Controllers\Member\CoachController as MemberCoachController;
use App\Http\Controllers\Member\MemberProfileController;
use App\Http\Controllers\Member\ProdukGymController;
use App\Http\Controllers\Member\KehadiranMemberController as MemberKehadiranMemberController;

/*
|--------------------------------------------------------------------------
| 1. RUTE PUBLIK / GUEST
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');


/*
|--------------------------------------------------------------------------
| 2. PENGALIH DASHBOARD (REDIRECTOR)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    if (Auth::user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    // default: member
    return redirect()->route('member.dashboard');
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
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // ================== DASHBOARD ==================
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // ================== INVENTARIS ALAT ==================
        Route::resource('inventaris', InventarisAlatController::class)
            ->parameters([
                'inventaris' => 'inventaris',
            ]);

        // ================== IZIN LATIHAN ====================
        Route::prefix('izin-latihan')->name('izin_latihan.')->group(function () {
            Route::get('/', [IzinLatihanController::class, 'index'])->name('index');
            Route::get('/riwayat', [IzinLatihanController::class, 'history'])->name('history');
            Route::get('/{id}/detail', [IzinLatihanController::class, 'show'])->name('detail');
            Route::get('/{izinLatihan}/setujui', [IzinLatihanController::class, 'approveForm'])->name('approve.form');
            Route::post('/{izinLatihan}/approve', [IzinLatihanController::class, 'approveIzin'])->name('approve');
            Route::post('/{id}/reject', [IzinLatihanController::class, 'reject'])->name('reject');

            Route::post('/store-manual', [IzinLatihanController::class, 'storeManual'])
                ->name('store.manual');
        });

        // =========================================================
        // RUTE MANAJEMEN PRODUK
        // =========================================================

        // 1. Penjualan produk
        Route::resource('penjualan_produk', PenjualanProdukController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);

        // 2. Master produk
        Route::resource('produk', ProdukController::class);

        // 3. Riwayat stok
        Route::prefix('stok_produk')->name('stok_produk.')->group(function () {
            Route::get('/riwayat', [StokProdukController::class, 'history'])->name('history');
            Route::get('/riwayat/{stokProduk}/detail', [StokProdukController::class, 'showHistoryDetail'])->name('history.detail');
        });

        // 4. Manajemen stok produk
        Route::resource('stok_produk', StokProdukController::class)
            ->parameters([
                'stok_produk' => 'stokProduk',
            ]);

        // =========================================================
        // RUTE MANAJEMEN COACH
        // =========================================================
        Route::resource('coaches', CoachController::class);

        // =========================================================
        // RUTE MANAJEMEN MEMBER
        // =========================================================
        Route::resource('members', MemberController::class);
        // admin.members.index, admin.members.store, admin.members.update, admin.members.destroy

        // =========================================================
        // RUTE MANAJEMEN MEMBERSHIP
        // =========================================================

        // Penjualan / transaksi membership
        Route::resource('memberships', MembershipController::class)
            ->only(['index', 'store', 'show', 'destroy']);

        // Master paket membership
        Route::resource('paket_memberships', PaketMembershipController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Membership group (anggota tambahan paket double/triple)
        Route::resource('membership_groups', MembershipGroupController::class)
            ->only(['index', 'store', 'destroy']);
        // admin.membership_groups.index, ...

        // =========================================================
        // RUTE MANAJEMEN PROFIL GYM
        // =========================================================
        Route::resource('profil_gym', ProfilGymController::class)
            ->parameters(['profil_gym' => 'profilGym']);

        // =========================================================
        // RUTE ABSENSI (ADMIN) – QR aktif + daftar kehadiran
        // =========================================================
        Route::prefix('absensi')->name('absensi.')->group(function () {
            // URL: /admin/absensi/kehadiran
            // Name: admin.absensi.kehadiran.index
            Route::get('kehadiran', [AdminKehadiranMemberController::class, 'index'])
                ->name('kehadiran.index');

            // URL: /admin/absensi/kehadiran/print
            // Name: admin.absensi.kehadiran.print
            Route::get('kehadiran/print', [AdminKehadiranMemberController::class, 'print'])
                ->name('kehadiran.print');
            
        });
    });


/*
|--------------------------------------------------------------------------
| 5. RUTE KHUSUS MEMBER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'member'])
    ->prefix('member')
    ->name('member.')
    ->group(function () {

        // ================== DASHBOARD ==================
        Route::get('dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');

        // ================== IZIN LATIHAN (Member) ==================
        Route::prefix('izin-latihan')->name('izin_latihan.')->group(function () {
            Route::get('/', [MemberIzinLatihanController::class, 'index'])->name('index');
            Route::get('/riwayat', [MemberIzinLatihanController::class, 'history'])->name('history');
            Route::get('/ajukan', [MemberIzinLatihanController::class, 'create'])->name('create');
            Route::post('/store', [MemberIzinLatihanController::class, 'store'])->name('store');
            Route::get('/{id}/detail', [MemberIzinLatihanController::class, 'detail'])->name('detail');
        });

        // ================== KEHADIRAN (Member – riwayat / status) ==================
        Route::prefix('kehadiran')->name('kehadiran.')->group(function () {
            // Dipakai sidebar: member.kehadiran.index
            Route::get('/', [MemberKehadiranMemberController::class, 'index'])
                ->name('index');
        });

        // ========= KEHADIRAN (halaman utama di sidebar) =========
        Route::get('kehadiran', [MemberKehadiranMemberController::class, 'index'])
            ->name('kehadiran.index');

        // ========= ABSENSI (scan QR & simpan) =========
        Route::prefix('absensi')->name('absensi.')->group(function () {
            // GET /member/absensi/scan?token=xxxx  -> dari QR
            Route::get('scan', [MemberKehadiranMemberController::class, 'scan'])
                ->name('scan');

            // POST /member/absensi -> simpan kehadiran
            Route::post('/', [MemberKehadiranMemberController::class, 'store'])
                ->name('store');

            // GET /member/absensi -> kalau ada yang akses langsung, redirect ke riwayat
            Route::get('/', function () {
                return redirect()->route('member.kehadiran.index');
            })->name('index_redirect'); // nama optional, boleh dihapus juga
        });

        // ================== PRODUK GYM (Marketplace) ==================
        Route::resource('produk_gym', ProdukGymController::class)
            ->only(['index', 'store'])
            ->names('produk_gym');

        // ================== DAFTAR COACH ==================
        Route::get('coach', [MemberCoachController::class, 'index'])->name('coach.index');

        // (opsional) rute pelengkapan profil bisa ditambahkan di sini
    });


/*
|--------------------------------------------------------------------------
| 6. RUTE AUTENTIKASI (Bawaan Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
