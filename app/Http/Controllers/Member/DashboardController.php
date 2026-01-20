<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\IzinLatihan;
use App\Models\KehadiranMember;
use App\Models\Produk;
use App\Models\Coach;
use App\Models\TransaksiMembership;
use App\Models\TransaksiMembershipMember;
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

        $today = Carbon::today()->startOfDay();
        $now   = Carbon::now();

        // ===============================
        // GREETING
        // ===============================
        $hour = (int) $now->hour;
        $greeting = match (true) {
            $hour < 11 => 'Selamat Pagi',
            $hour < 15 => 'Selamat Siang',
            $hour < 18 => 'Selamat Sore',
            default    => 'Selamat Malam',
        };

        // ===============================
        // MEMBERSHIP (CANONICAL) dari transaksi_membership_members
        // ===============================
        $mulai = null;
        $akhir = null;
        $membershipAktif = false;

        $sisaHari = 0;
        $totalDurasi = 0;
        $hariTerpakai = 0;
        $progressUsedPercent = 0;

        $latestMembership = null;

        $hasMembershipTables =
            Schema::hasTable('transaksi_memberships') &&
            Schema::hasTable('transaksi_membership_members');

        if ($hasMembershipTables) {

            $pivotBase = TransaksiMembershipMember::query()
                ->join('transaksi_memberships as tm', 'tm.id', '=', 'transaksi_membership_members.transaksi_membership_id')
                ->whereNull('tm.canceled_at')
                ->where('transaksi_membership_members.member_id', (int) $member->id);

            $activePivot = (clone $pivotBase)
                ->whereDate('transaksi_membership_members.tanggal_mulai', '<=', $today->toDateString())
                ->whereDate('transaksi_membership_members.tanggal_akhir', '>=', $today->toDateString())
                ->orderByDesc('transaksi_membership_members.tanggal_akhir')
                ->select([
                    'transaksi_membership_members.transaksi_membership_id',
                    'transaksi_membership_members.member_id',
                    'transaksi_membership_members.tanggal_mulai',
                    'transaksi_membership_members.tanggal_akhir',
                ])
                ->first();

            $refPivot = $activePivot ?: (clone $pivotBase)
                ->orderByDesc('transaksi_membership_members.tanggal_akhir')
                ->select([
                    'transaksi_membership_members.transaksi_membership_id',
                    'transaksi_membership_members.member_id',
                    'transaksi_membership_members.tanggal_mulai',
                    'transaksi_membership_members.tanggal_akhir',
                ])
                ->first();

            if ($refPivot) {
                $mulai = Carbon::parse($refPivot->tanggal_mulai)->startOfDay();
                $akhir = Carbon::parse($refPivot->tanggal_akhir)->endOfDay();

                $membershipAktif = (bool) $activePivot;

                if ($mulai && $akhir) {
                    $mulaiDay = $mulai->copy()->startOfDay();
                    $akhirDay = $akhir->copy()->startOfDay();

                    $totalDurasi = $mulaiDay->diffInDays($akhirDay) + 1;

                    if ($membershipAktif) {
                        $hariTerpakai = $mulaiDay->diffInDays($today) + 1;
                        $sisaHari     = $today->diffInDays($akhirDay) + 1;

                        $hariTerpakai = max(0, min($totalDurasi, $hariTerpakai));
                        $sisaHari     = max(0, min($totalDurasi, $sisaHari));
                    } else {
                        if ($today->gt($akhirDay)) {
                            $hariTerpakai = $totalDurasi;
                            $sisaHari     = 0;
                        } else {
                            // belum mulai (edge case)
                            $hariTerpakai = 0;
                            $sisaHari     = 0;
                        }
                    }
                }
            }

            $progressUsedPercent = $totalDurasi > 0
                ? (int) max(0, min(100, round(($hariTerpakai / $totalDurasi) * 100)))
                : 0;

            $latestMembership = TransaksiMembership::with('paket')
                ->valid()
                ->whereHas('participants', fn ($p) => $p->where('member_id', (int) $member->id))
                ->orderByDesc('tanggal_transaksi')
                ->orderByDesc('id')
                ->first();

            if ($latestMembership) {
                $pivotForThisTrx = TransaksiMembershipMember::query()
                    ->where('transaksi_membership_id', (int) $latestMembership->id)
                    ->where('member_id', (int) $member->id)
                    ->orderByDesc('tanggal_akhir')
                    ->first();

                if ($pivotForThisTrx) {
                    $latestMembership->tanggal_mulai = Carbon::parse($pivotForThisTrx->tanggal_mulai);
                    $latestMembership->tanggal_akhir = Carbon::parse($pivotForThisTrx->tanggal_akhir);
                }
            }

        } else {
            // fallback kalau tabel transaksi belum ada
            $mulai = $member->tanggal_mulai ? Carbon::parse($member->tanggal_mulai)->startOfDay() : null;
            $akhir = $member->tanggal_akhir ? Carbon::parse($member->tanggal_akhir)->endOfDay() : null;

            $membershipAktif = (bool) ($member->membership_aktif ?? false);

            if ($mulai && $akhir) {
                $mulaiDay = $mulai->copy()->startOfDay();
                $akhirDay = $akhir->copy()->startOfDay();

                $totalDurasi = $mulaiDay->diffInDays($akhirDay) + 1;

                if ($today->between($mulaiDay, $akhirDay, true)) {
                    $membershipAktif = true;
                    $hariTerpakai = $mulaiDay->diffInDays($today) + 1;
                    $sisaHari     = $today->diffInDays($akhirDay) + 1;
                } elseif ($today->gt($akhirDay)) {
                    $membershipAktif = false;
                    $hariTerpakai = $totalDurasi;
                    $sisaHari = 0;
                }

                $hariTerpakai = max(0, min($totalDurasi, $hariTerpakai));
                $sisaHari     = max(0, min($totalDurasi, $sisaHari));

                $progressUsedPercent = $totalDurasi > 0
                    ? (int) max(0, min(100, round(($hariTerpakai / $totalDurasi) * 100)))
                    : 0;
            }
        }

        // ===============================
        // IZIN LATIHAN
        // ===============================
        $izinBase = IzinLatihan::where('member_id', (int) $member->id);

        $izinStats = [
            'pending'   => (clone $izinBase)->where('status', 'pending')->count(),
            'disetujui' => (clone $izinBase)->where('status', 'disetujui')->count(),
            'ditolak'   => (clone $izinBase)->where('status', 'ditolak')->count(),
            'total'     => $izinBase->count(),
        ];

        $recentIzin = IzinLatihan::where('member_id', (int) $member->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // ===============================
        // KEHADIRAN
        // ===============================
        $absensiBase = KehadiranMember::where('member_id', (int) $member->id);

        $todayAttendance = (clone $absensiBase)
            ->whereDate('tanggal', $today->toDateString())
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

        // ===============================
        // PRODUK (RANDOM seperti coach)
        // ===============================
        $produkRekomendasi = Produk::query()
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // ===============================
        // COACH (RANDOM)
        // ===============================
        $coaches = Coach::query()
            ->inRandomOrder()
            ->limit(4)
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

            'produkRekomendasi'   => $produkRekomendasi,
            'coaches'             => $coaches,
        ]);
    }
}
