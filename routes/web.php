<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\GuestCoachController;
use App\Http\Controllers\GuestMembershipController;


// Controller Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IzinLatihanController;
use App\Http\Controllers\Admin\InventarisAlatController;
use App\Http\Controllers\Admin\TransaksiProdukController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\StokProdukController;
use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\LatihanHarianController;
use App\Http\Controllers\Admin\LaporanKeuanganController;
use App\Http\Controllers\Admin\LaporanKehadiranController;

// Controller Membership (Admin)
use App\Http\Controllers\Admin\PaketMembershipController;
use App\Http\Controllers\Admin\TransaksiMembershipController;
use App\Http\Controllers\Admin\ProfilGymController;

// Controller Rekening & QRIS (Admin)
use App\Http\Controllers\Admin\RekeningController;
use App\Http\Controllers\Admin\InfoRekeningController;
use App\Http\Controllers\Admin\InfoQrisController;

// Controller Absensi (Admin)
use App\Http\Controllers\Admin\KehadiranMemberController as AdminKehadiranMemberController;

// Controller Laporan / Analitik (Admin)
use App\Http\Controllers\Admin\AbsensiReportController;

// Controller Member
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\IzinLatihanController as MemberIzinLatihanController;
use App\Http\Controllers\Member\CoachController as MemberCoachController;
use App\Http\Controllers\Member\ProdukGymController;
use App\Http\Controllers\Member\KehadiranMemberController as MemberKehadiranMemberController;
use App\Http\Controllers\Member\MemberMembershipHistoryController;
use App\Http\Middleware\SyncMemberCartToSession;


// Notifikasi (Admin)
use App\Http\Controllers\Admin\AdminNotifikasiController;

// Notifikasi (Member)
use App\Http\Controllers\Member\MemberNotifikasiController;

/*
|--------------------------------------------------------------------------
| 1. RUTE PUBLIK / GUEST
|--------------------------------------------------------------------------
*/

Route::name('guest.')->group(function () {

    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // === MEMBERSHIP ROUTES ===
    Route::get('/membership', [GuestMembershipController::class, 'index'])->name('membership.index');

    // === MARKETPLACE ROUTES ===

    // 1. Index (Katalog)
    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');

    // === COACH ROUTES ===
    Route::get('/coaches', [GuestCoachController::class, 'index'])->name('coaches.index');
    Route::get('/coaches/{slug}', [GuestCoachController::class, 'show'])->name('coaches.show');
});


// ==========================================
// MARKETPLACE CART & ACTIONS (MENGARAHKAN KE LOGIN)
// Pengunjung harus login untuk mengakses keranjang dan transaksi
// ==========================================
Route::get('/marketplace/cart', function () {
    return redirect()->route('login');
})->name('guest.marketplace.cart');

Route::get('/cart', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    // Tambah ke Keranjang
    Route::post('/cart/add/{id}', [MarketplaceController::class, 'addToCart'])
        ->name('cart.add');
    Route::post('/guest/cart/add/{id}', [MarketplaceController::class, 'addToCart'])
        ->name('guest.marketplace.cart.add');

    // Hapus dari Keranjang
    Route::post('/cart/remove/{id}', [MarketplaceController::class, 'removeFromCart'])
        ->name('guest.marketplace.cart.remove');
    Route::get('/cart/remove/{id}', [MarketplaceController::class, 'removeFromCart']);

    // Update Qty
    Route::post('/cart/{id}/quantity', [MarketplaceController::class, 'updateCartQuantity'])
        ->name('guest.marketplace.cart.quantity');
    Route::get('/cart/{id}/quantity', function () {
        return redirect()->route('login');
    });
});

// Detail Produk Publik (Wildcard {slug} diletakkan setelah /marketplace/cart)
Route::get('/marketplace/{slug}', [MarketplaceController::class, 'show'])
    ->where('slug', '^[0-9]+-.*$')
    ->name('guest.marketplace.show');


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

    return redirect()->route('member.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| 3. RUTE PROFIL (UMUM)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('profile')
    ->name('profile.')
    ->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
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

        // ================== NOTIFIKASI (ADMIN) ==================
        Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
            Route::get('/', [AdminNotifikasiController::class, 'index'])->name('index');
            Route::post('/read-all', [AdminNotifikasiController::class, 'readAll'])->name('read_all');
            Route::post('/{id}/read', [AdminNotifikasiController::class, 'readOne'])->name('read_one');
            Route::post('/{id}/hide', [AdminNotifikasiController::class, 'hideOne'])->name('hide_one');
            Route::post('/hide-all', [AdminNotifikasiController::class, 'hideAll'])->name('hide_all');
            Route::get('/{id}/go', [AdminNotifikasiController::class, 'go'])->name('go');

            // ===== realtime polling endpoint =====
            Route::get('/poll', [AdminNotifikasiController::class, 'poll'])->name('poll');
        });

        // ================== INVENTARIS ALAT ==================
        Route::resource('inventaris', InventarisAlatController::class)
            ->parameters(['inventaris' => 'inventaris']);

        // ================== IZIN LATIHAN ====================
        Route::prefix('izin-latihan')->name('izin_latihan.')->group(function () {
            Route::get('/', [IzinLatihanController::class, 'index'])->name('index');
            Route::get('/riwayat', [IzinLatihanController::class, 'history'])->name('history');
            Route::get('/{id}/detail', [IzinLatihanController::class, 'show'])->name('detail');
            Route::get('/{izinLatihan}/setujui', [IzinLatihanController::class, 'approveForm'])->name('approve.form');
            Route::post('/{izinLatihan}/approve', [IzinLatihanController::class, 'approveIzin'])->name('approve');
            Route::post('/{id}/reject', [IzinLatihanController::class, 'reject'])->name('reject');

            Route::post('/store-manual', [IzinLatihanController::class, 'storeManual'])->name('store.manual');
        });

        // =========================================================
        // RUTE MANAJEMEN PRODUK
        // =========================================================

        /**
         * TRANSAKSI PRODUK (Kasir + Riwayat)
         * Penting: route /riwayat harus didefinisikan sebelum route parameter {transaksiProduk}.
         */
        Route::prefix('transaksi-produk')->name('transaksi_produk.')->group(function () {
            Route::get('/', [TransaksiProdukController::class, 'index'])->name('index');
            Route::post('/', [TransaksiProdukController::class, 'store'])->name('store');

            Route::get('/riwayat', [TransaksiProdukController::class, 'history'])->name('history');

            // CETAK STRUK (HTML print)
            Route::get('/{transaksiProduk}/cetak', [TransaksiProdukController::class, 'cetak'])
                ->name('cetak');

            Route::delete('/{transaksiProduk}', [TransaksiProdukController::class, 'destroy'])->name('destroy');
        });

        // Produk master
        Route::resource('produk', ProdukController::class);

        // Riwayat stok (custom)
        Route::prefix('stok_produk')->name('stok_produk.')->group(function () {
            Route::get('/riwayat', [StokProdukController::class, 'history'])->name('history');
            Route::get('/riwayat/{stokProduk}/detail', [StokProdukController::class, 'showHistoryDetail'])->name('history.detail');
        });

        // CRUD stok
        Route::resource('stok_produk', StokProdukController::class)
            ->parameters(['stok_produk' => 'stokProduk']);

        // =========================================================
        // RUTE MANAJEMEN COACH
        // =========================================================
        Route::resource('coaches', CoachController::class);

        // =========================================================
        // RUTE MANAJEMEN MEMBER
        // =========================================================
        Route::resource('members', MemberController::class);

        // =========================================================
        // RUTE MANAJEMEN MEMBERSHIP (BARU)
        // URL: kebab-case, route name: snake_case
        // =========================================================

        // Transaksi membership (pembayaran + kompensasi manual + detail + cancel)
        Route::prefix('transaksi-membership')->name('transaksi_membership.')->group(function () {

            // KOMPENSASI MANUAL (bonus/trial admin) - harus route baru
            Route::post('/kompensasi-manual', [TransaksiMembershipController::class, 'storeKompensasiManual'])
                ->name('kompensasi_manual.store');

            // PEMBAYARAN (create dari modal pembayaran)
            Route::get('/', [TransaksiMembershipController::class, 'index'])->name('index');
            Route::post('/', [TransaksiMembershipController::class, 'store'])->name('store');

            // DETAIL (kalau memang dipakai via route, optional)
            Route::get('/{transaksiMembership}', [TransaksiMembershipController::class, 'show'])->name('show');

            // CANCEL (destroy)
            Route::delete('/{transaksiMembership}', [TransaksiMembershipController::class, 'destroy'])->name('destroy');
        });


        // Master paket membership
        Route::resource('paket_memberships', PaketMembershipController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // =========================================================
        // RUTE LATIHAN HARIAN
        // =========================================================
        Route::resource('latihan-harian', LatihanHarianController::class)
            ->parameters(['latihan-harian' => 'latihanHarian'])
            ->names('latihan_harian');

        // =========================================================
        // RUTE MANAJEMEN PROFIL GYM
        // =========================================================
        Route::resource('profil_gym', ProfilGymController::class)
            ->parameters(['profil_gym' => 'profilGym']);



        // =========================================================
        // REKENING (INDEX GABUNGAN) + CRUD REKENING/QRIS
        // =========================================================
        Route::resource('rekening', RekeningController::class)->only(['index']);

        Route::resource('info-rekening', InfoRekeningController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['info-rekening' => 'infoRekening']);

        Route::resource('info-qris', InfoQrisController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['info-qris' => 'infoQris']);

        // =========================================================
        // RUTE ABSENSI (ADMIN)
        // =========================================================
        Route::prefix('absensi')->name('absensi.')->group(function () {
            Route::get('/', [AdminKehadiranMemberController::class, 'index'])->name('index');
            Route::get('/print', [AdminKehadiranMemberController::class, 'print'])->name('print');
        });
        // =========================================================
        // LAPORAN / ANALITIK (ADMIN)
        // =========================================================
        Route::prefix('laporan')->name('laporan.')->group(function () {

            // Tidak dibuat redirect. Kalau belum ada halaman index laporan, biarkan tidak ada.

            // Laporan Absensi
            // Route::get('/absensi', [AbsensiReportController::class, 'index'])
            //     ->name('absensi.index');
            // Route::get('/absensi/pdf', [AbsensiReportController::class, 'exportPdf'])
            //     ->name('absensi.pdf');
            Route::prefix('kehadiran')->name('kehadiran.')->group(function () {
                Route::get('/', [LaporanKehadiranController::class, 'index'])->name('index');
                Route::get('/absensi', [LaporanKehadiranController::class, 'absensi'])->name('absensi');
                Route::get('/kompensasi', [LaporanKehadiranController::class, 'kompensasi'])->name('kompensasi');
                Route::get('/audit', [LaporanKehadiranController::class, 'audit'])->name('audit');
                Route::get('/excel', [LaporanKehadiranController::class, 'excel'])->name('excel');
                Route::get('/pdf', [LaporanKehadiranController::class, 'pdf'])->name('pdf');
            });
            // ===== Laporan Keuangan (BARU) =====
            Route::prefix('keuangan')->name('keuangan.')->group(function () {
                Route::get('/', [LaporanKeuanganController::class, 'index'])->name('index');
                Route::get('/excel', [LaporanKeuanganController::class, 'excel'])->name('excel');
                Route::get('/pdf', [LaporanKeuanganController::class, 'pdf'])->name('pdf');
                Route::get('/produk', [LaporanKeuanganController::class, 'produk'])->name('produk');
                Route::get('/produk/pdf', [LaporanKeuanganController::class, 'pdfProduk'])->name('produk.pdf');
                Route::get('/produk/excel', [LaporanKeuanganController::class, 'excelProduk'])->name('produk.excel');
                Route::get('/membership', [LaporanKeuanganController::class, 'membership'])->name('membership');
                Route::get('/membership/pdf', [LaporanKeuanganController::class, 'pdfMembership'])->name('membership.pdf');
                Route::get('/membership/excel', [LaporanKeuanganController::class, 'excelMembership'])->name('membership.excel');
                Route::get('/harian', [LaporanKeuanganController::class, 'harian'])->name('harian');
                Route::get('/harian/pdf', [LaporanKeuanganController::class, 'pdfHarian'])->name('harian.pdf');
                Route::get('/harian/excel', [LaporanKeuanganController::class, 'excelHarian'])->name('harian.excel');
                Route::get('/gabungan', [LaporanKeuanganController::class, 'gabungan'])->name('gabungan');
            });

            // Placeholder (sementara belum ada)
            Route::get('/membership', fn() => abort(404))->name('membership.index');
            Route::get('/produk', fn() => abort(404))->name('produk.index');
            Route::get('/stok', fn() => abort(404))->name('stok.index');
            Route::get('/member', fn() => abort(404))->name('member.index');
        });
    });

/*
|--------------------------------------------------------------------------
| 5. RUTE KHUSUS MEMBER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'member', SyncMemberCartToSession::class])
    ->prefix('member')
    ->name('member.')
    ->group(function () {


        Route::get('dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('izin-latihan')->name('izin_latihan.')->group(function () {
            Route::get('/', [MemberIzinLatihanController::class, 'index'])->name('index');
            Route::get('/riwayat', [MemberIzinLatihanController::class, 'history'])->name('history');
            Route::get('/ajukan', [MemberIzinLatihanController::class, 'create'])->name('create');
            Route::post('/store', [MemberIzinLatihanController::class, 'store'])->name('store');
            Route::get('/{id}/detail', [MemberIzinLatihanController::class, 'detail'])->name('detail');
        });

        Route::prefix('kehadiran')->name('kehadiran.')->group(function () {
            Route::get('/', [MemberKehadiranMemberController::class, 'index'])->name('index');
        });

        Route::prefix('absensi')->name('absensi.')->group(function () {
            Route::get('scan/{token}', [MemberKehadiranMemberController::class, 'scan'])
                ->name('scan');

            Route::post('scan/{token}', [MemberKehadiranMemberController::class, 'store'])
                ->name('store');

            Route::get('/', function () {
                return redirect()->route('member.kehadiran.index');
            })->name('index_redirect');
        });

        // =========================
        // Marketplace (Index + Show)
        // =========================
        Route::get('/produk_gym', [ProdukGymController::class, 'index'])
            ->name('produk_gym.index');

        Route::get('/produk_gym/{slug}', [ProdukGymController::class, 'show'])
            ->where('slug', '^[0-9]+-.*$')
            ->name('produk_gym.show');

        // =========================
        // Cart & Payment (Member)
        // =========================
        Route::get('/produk_gym/cart', [ProdukGymController::class, 'cart'])
            ->name('produk_gym.cart');

        Route::get('/produk_gym/pembayaran', [ProdukGymController::class, 'payment'])
            ->name('produk_gym.payment');

        Route::get('/produk_gym/checkout', [ProdukGymController::class, 'payment'])
            ->name('produk_gym.checkout');

        Route::post('/produk_gym/cart/add/{id}', [ProdukGymController::class, 'addToCart'])
            ->whereNumber('id')
            ->name('produk_gym.cart.add');

        Route::post('/produk_gym/buy-now/{id}', [ProdukGymController::class, 'buyNow'])
            ->whereNumber('id')
            ->name('produk_gym.buy_now');

        // AJAX remove (sesuai JS Anda yang POST)
        Route::post('/produk_gym/cart/remove/{id}', [ProdukGymController::class, 'removeFromCart'])
            ->whereNumber('id')
            ->name('produk_gym.cart.remove');

        // AJAX qty update (sesuai JS Anda yang POST ke .../quantity)
        Route::post('/produk_gym/cart/{id}/quantity', [ProdukGymController::class, 'updateCartQuantity'])
            ->whereNumber('id')
            ->name('produk_gym.cart.qty');


        /**
         * COACH: index + show (slug)
         * URL:
         * - /member/coach
         * - /member/coach/{id}-{nama-coach}
         */
        Route::prefix('coach')->name('coach.')->group(function () {
            Route::get('/', [MemberCoachController::class, 'index'])->name('index');

            Route::get('/{slug}', [MemberCoachController::class, 'show'])
                ->where('slug', '^[0-9]+-[A-Za-z0-9\-]+$')
                ->name('show');
        });

        // ================== NOTIFIKASI (MEMBER) ==================
        Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
            Route::get('/', [MemberNotifikasiController::class, 'index'])->name('index');

            Route::post('/read-all', [MemberNotifikasiController::class, 'readAll'])->name('read_all');
            Route::post('/{id}/read', [MemberNotifikasiController::class, 'readOne'])->name('read_one');

            Route::post('/{id}/hide', [MemberNotifikasiController::class, 'hideOne'])->name('hide_one');
            Route::post('/hide-all', [MemberNotifikasiController::class, 'hideAll'])->name('hide_all');

            Route::get('/{id}/go', [MemberNotifikasiController::class, 'go'])->name('go');

            // realtime polling endpoint (opsional, sama seperti admin)
            Route::get('/poll', [MemberNotifikasiController::class, 'poll'])->name('poll');
        });
        Route::prefix('membership')->name('membership.')->group(function () {

            // INDEX
            Route::get('/', [\App\Http\Controllers\Member\PaketMembershipController::class, 'index'])
                ->name('index');

            Route::get('/{paketMembership}/checkout', [\App\Http\Controllers\Member\PaketMembershipController::class, 'checkout'])
                ->whereNumber('paketMembership')
                ->name('checkout');

            Route::get('/{paketMembership}/pembayaran', [\App\Http\Controllers\Member\PaketMembershipController::class, 'checkout'])
                ->whereNumber('paketMembership')
                ->name('payment');

            // DETAIL PAKET MEMBERSHIP
            Route::get('/{paketMembership}', [\App\Http\Controllers\Member\PaketMembershipController::class, 'show'])
                ->whereNumber('paketMembership')
                ->name('show');
            Route::get('/riwayat', [\App\Http\Controllers\Member\MemberMembershipHistoryController::class, 'index'])
                ->name('history');
        });
    });




/*
|--------------------------------------------------------------------------
| 6. RUTE AUTENTIKASI (Bawaan Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
