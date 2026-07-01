<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

// Models
use App\Models\Notification;
use App\Models\IzinLatihan;
use App\Models\Produk;
use App\Models\TransaksiProduk;
use App\Models\TransaksiMembership;
use App\Models\TransaksiMembershipMember;
use App\Models\PaketMembership;
use App\Models\KehadiranMember;
use App\Models\AbsensiPeriode;
use App\Models\Coach;
use App\Models\InfoQris;
use App\Models\InfoRekening;
use App\Models\ProfilGym;

// Observers
use App\Observers\NotificationObserver;
use App\Observers\IzinLatihanObserver;
use App\Observers\ProdukObserver;
use App\Observers\TransaksiProdukObserver;
use App\Observers\TransaksiMembershipObserver;
use App\Observers\TransaksiMembershipMemberObserver;
use App\Observers\PaketMembershipObserver;
use App\Observers\KehadiranMemberObserver;
use App\Observers\AbsensiPeriodeObserver;
use App\Observers\CoachObserver;
use App\Observers\InfoQrisObserver;
use App\Observers\InfoRekeningObserver;
use App\Observers\ProfilGymObserver;

// Service
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Singleton service (boleh seperti ini)
        $this->app->singleton(NotificationService::class, fn () => new NotificationService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * ==========================================================
         * FORCE HTTPS
         * ==========================================================
         */
        if (config('app.env') === 'production' || str_contains(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        /**
         * ==========================================================
         * REGISTER OBSERVERS
         * ==========================================================
         */
        Notification::observe(NotificationObserver::class);
        IzinLatihan::observe(IzinLatihanObserver::class);

        Produk::observe(ProdukObserver::class);
        TransaksiProduk::observe(TransaksiProdukObserver::class);
        TransaksiMembership::observe(TransaksiMembershipObserver::class);
        TransaksiMembershipMember::observe(TransaksiMembershipMemberObserver::class);
        PaketMembership::observe(PaketMembershipObserver::class);

        KehadiranMember::observe(KehadiranMemberObserver::class);
        AbsensiPeriode::observe(AbsensiPeriodeObserver::class);

        Coach::observe(CoachObserver::class);
        InfoQris::observe(InfoQrisObserver::class);
        InfoRekening::observe(InfoRekeningObserver::class);
        ProfilGym::observe(ProfilGymObserver::class);

        /**
         * ==========================================================
         * Navbar admin: badge unread notifikasi
         * ==========================================================
         */
        View::composer('components.admin.navbar', function ($view) {
            $user  = auth()->user();
            $count = 0;

            if ($user && strtolower((string)($user->role ?? '')) === 'admin') {
                $adminId = (int) $user->id;

                $base = Notification::query()
                    ->where('target', 'user')
                    ->where('target_user_id', $adminId)
                    ->whereNotIn('id', function ($sub) use ($adminId) {
                        $sub->select('notification_id')
                            ->from('notification_deletes')
                            ->where('user_id', $adminId);
                    });

                $count = (clone $base)
                    ->whereNotIn('id', function ($sub) use ($adminId) {
                        $sub->select('notification_id')
                            ->from('notification_reads')
                            ->where('user_id', $adminId);
                    })
                    ->count();
            }

            $view->with('notificationCount', $count);
        });
    }
}
