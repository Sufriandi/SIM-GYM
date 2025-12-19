<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\PaketMembership;
use App\Models\TransaksiMembership;
use App\Models\TransaksiMembershipMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransaksiMembershipSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) return;

        $pakets = PaketMembership::all();
        if ($pakets->isEmpty()) return;

        $members = Member::with('user')->get();
        if ($members->isEmpty()) return;

        // contoh buat 50 transaksi random
        for ($i = 0; $i < 50; $i++) {
            DB::transaction(function () use ($admin, $pakets, $members) {

                $paket = $pakets->random();
                $buyer = $members->random();

                $tanggalTransaksi = Carbon::today()->subDays(rand(0, 90))->setTime(rand(8, 20), rand(0, 59));
                $transDay = $tanggalTransaksi->copy()->startOfDay();
                $buyerId = (int) $buyer->id;

                // auto-extend untuk buyer (cek histori transaksi member tersebut)
                $lastEnd = TransaksiMembership::notCanceled()
                    ->where(function ($q) use ($buyerId) {
                        $q->where('buyer_member_id', $buyerId)
                            ->orWhereHas('participants', fn($p) => $p->where('member_id', $buyerId));
                    })
                    ->orderByDesc('tanggal_akhir')
                    ->value('tanggal_akhir');

                $tanggalMulai = $lastEnd
                    ? (Carbon::parse($lastEnd)->startOfDay()->gte($transDay)
                        ? Carbon::parse($lastEnd)->startOfDay()->addDay()
                        : $transDay)
                    : $transDay;

                $tanggalAkhir = $tanggalMulai->copy()->addDays($paket->durasi - 1);

                $trx = TransaksiMembership::create([
                    'buyer_member_id'   => $buyerId,
                    'created_by'        => $admin->id,
                    'paket_id'          => $paket->id,
                    'tanggal_transaksi' => $tanggalTransaksi,
                    'tanggal_mulai'     => $tanggalMulai,
                    'tanggal_akhir'     => $tanggalAkhir,
                    'jenis_transaksi'   => 'sale',
                    'metode_pembayaran' => ['cash', 'transfer', 'qris'][array_rand(['cash', 'transfer', 'qris'])],
                    'keterangan'        => 'Seeder demo',
                ]);

                // primary
                TransaksiMembershipMember::create([
                    'transaksi_membership_id' => $trx->id,
                    'member_id'               => $buyerId,
                    'role'                    => 'primary',
                ]);

                // peserta tambahan sesuai tipe paket
                $maxAdditional = match ($paket->tipe) {
                    'single' => 0,
                    'double' => 1,
                    'triple' => 2,
                    default => 0,
                };

                if ($maxAdditional > 0) {
                    $others = $members->where('id', '!=', $buyerId)->random(min($maxAdditional, $members->count() - 1));

                    foreach (collect($others) as $m) {
                        TransaksiMembershipMember::create([
                            'transaksi_membership_id' => $trx->id,
                            'member_id'               => (int) $m->id,
                            'role'                    => 'member',
                        ]);
                    }
                }

                // NOTE: tidak ada update ke tabel members
            });
        }
    }
}
