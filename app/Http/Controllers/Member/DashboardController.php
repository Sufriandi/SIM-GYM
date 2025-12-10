<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Membership;
use App\Models\IzinLatihan;
use App\Models\KehadiranMember;
use App\Models\Produk;
use App\Models\Coach;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Silakan login terlebih dahulu.');
        }

        /** @var Member|null $member */
        $member = $user->member ?? null;

        if (! $member) {
            abort(403, 'Akun ini belum terhubung dengan data member.');
        }

        $today = Carbon::today();
        $now   = Carbon::now();

        // ===============================
        // GREETING
        // ===============================
        $hour = $now->hour;
        if ($hour < 11) {
            $greeting = 'Selamat Pagi';
        } elseif ($hour < 15) {
            $greeting = 'Selamat Siang';
        } elseif ($hour < 18) {
            $greeting = 'Selamat Sore';
        } else {
            $greeting = 'Selamat Malam';
        }

        // ====================================
        // INFO MEMBERSHIP DARI TABEL MEMBERS
        // ====================================
        $mulai = $member->tanggal_mulai
            ? Carbon::parse($member->tanggal_mulai)->startOfDay()
            : null;

        $akhir = $member->tanggal_akhir
            ? Carbon::parse($member->tanggal_akhir)->endOfDay()
            : null;

        $membershipAktif = $member->membership_aktif; // accessor di model Member

        $sisaHari     = 0;
        $totalDurasi  = 0;
        $hariTerpakai = 0;

        if ($mulai && $akhir) {
            // Total durasi membership (hari)
            $totalDurasi = $mulai->diffInDays($akhir) + 1;

            // Hari terpakai
            if ($today->gte($mulai)) {
                $cutoff       = $today->lt($akhir) ? $today : $akhir;
                $hariTerpakai = $mulai->diffInDays($cutoff) + 1;
            }

            // Sisa hari (kalau lewat, jadi 0)
            $sisaHari = max(0, $akhir->diffInDays($today, false));
        }

        $progressUsedPercent = $totalDurasi > 0
            ? max(0, min(100, round(($hariTerpakai / $totalDurasi) * 100)))
            : 0;

        // ====================================
        // MEMBERSHIP TRANSAKSI TERAKHIR
        // (di-guard pakai Schema::hasColumn supaya tidak error
        //  jika tabel memberships belum punya kolom member_id)
        // ====================================
        $latestMembership = null;

        if (Schema::hasTable('memberships') && Schema::hasColumn('memberships', 'member_id')) {
            $latestMembership = Membership::with('paket')
                ->where('member_id', $member->id)
                ->whereNull('canceled_at')
                ->orderByDesc('tanggal_transaksi')
                ->first();
        }

        // ====================================
        // STATISTIK IZIN LATIHAN
        // ====================================
        $izinBase = IzinLatihan::where('member_id', $member->id);

        $izinStats = [
            'pending'   => (clone $izinBase)->where('status', 'pending')->count(),
            'disetujui' => (clone $izinBase)->where('status', 'disetujui')->count(),
            'ditolak'   => (clone $izinBase)->where('status', 'ditolak')->count(),
            'total'     => $izinBase->count(),
        ];

        $recentIzin = IzinLatihan::where('member_id', $member->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // ====================================
        // STATISTIK ABSENSI / KEHADIRAN
        // ====================================
        $absensiBase = KehadiranMember::where('member_id', $member->id);

        $todayAttendance = (clone $absensiBase)
            ->whereDate('tanggal', $today)
            ->orderByDesc('jam_masuk')
            ->first();

        $monthStart = $today->copy()->startOfMonth();
        $monthEnd   = $today->copy()->endOfMonth();
        $yearStart  = $today->copy()->startOfYear();
        $yearEnd    = $today->copy()->endOfYear();

        $hadirBulanIni = (clone $absensiBase)
            ->whereBetween('tanggal', [$monthStart, $monthEnd])
            ->count();

        $hadirTahunIni = (clone $absensiBase)
            ->whereBetween('tanggal', [$yearStart, $yearEnd])
            ->count();

        $recentAttendance = (clone $absensiBase)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_masuk')
            ->limit(5)
            ->get();

        // ====================================
        // PRODUK GYM (TERBARU & TERLARIS)
        // ====================================
        $produkTerbaru = Produk::orderByDesc('created_at')
            ->limit(4)
            ->get();

        $produkTerlaris = Produk::withCount('penjualan')
            ->orderByDesc('penjualan_count')
            ->limit(4)
            ->get();

        // ====================================
        // COACH (RANDOM)
        // ====================================
        $coaches = Coach::inRandomOrder()
            ->limit(3)
            ->get();

        return view('member.dashboard', [
            'user'                => $user,
            'member'              => $member,
            'greeting'            => $greeting,
            'membershipAktif'     => $membershipAktif,
            'mulai'               => $mulai,
            'akhir'               => $akhir,
            'sisaHari'            => $sisaHari,
            'totalDurasi'         => $totalDurasi,
            'hariTerpakai'        => $hariTerpakai,
            'progressUsedPercent' => $progressUsedPercent,
            'latestMembership'    => $latestMembership,
            'izinStats'           => $izinStats,
            'recentIzin'          => $recentIzin,
            'todayAttendance'     => $todayAttendance,
            'hadirBulanIni'       => $hadirBulanIni,
            'hadirTahunIni'       => $hadirTahunIni,
            'recentAttendance'    => $recentAttendance,
            'produkTerbaru'       => $produkTerbaru,
            'produkTerlaris'      => $produkTerlaris,
            'coaches'             => $coaches,
        ]);
    }
}
