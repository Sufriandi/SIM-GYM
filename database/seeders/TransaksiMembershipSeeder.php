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
        if ($members->count() < 1) return;

        for ($i = 0; $i < 50; $i++) {
            DB::transaction(function () use ($admin, $pakets, $members) {

                $paket = $pakets->random();
                $buyer = $members->random();
                $buyerId = (int) $buyer->id;

                // Tentukan jumlah peserta tambahan sesuai tipe paket
                $maxAdditional = match ($paket->tipe) {
                    'single' => 0,
                    'double' => 1,
                    'triple' => 2,
                    default  => 0,
                };

                // Pilih peserta tambahan dulu (agar perhitungan tanggal_mulai mempertimbangkan semua member yang terlibat)
                $participantIds = [];
                if ($maxAdditional > 0 && $members->count() > 1) {
                    $others = $members->where('id', '!=', $buyerId);

                    // random() bisa mengembalikan Model atau Collection, jadi kita normalisasi jadi array id
                    $picked = $others->random(min($maxAdditional, $others->count()));

                    $participantIds = collect($picked)
                        ->pluck('id')
                        ->map(fn($v) => (int) $v)
                        ->unique()
                        ->values()
                        ->all();
                }

                // Semua member yang terlibat transaksi ini (untuk hitung lastEnd)
                $allMemberIds = array_values(array_unique(array_merge([$buyerId], $participantIds)));

                $tanggalTransaksi = Carbon::today()
                    ->subDays(rand(0, 90))
                    ->setTime(rand(8, 20), rand(0, 59));

                $transDay = $tanggalTransaksi->copy()->startOfDay();

                // lastEnd terjauh dari transaksi valid yang melibatkan salah satu member (buyer/participant)
                $lastEnd = TransaksiMembership::query()
                    ->valid()
                    ->where(function ($q) use ($allMemberIds) {
                        $q->whereIn('buyer_member_id', $allMemberIds)
                            ->orWhereHas('participants', fn($p) => $p->whereIn('member_id', $allMemberIds));
                    })
                    ->max('tanggal_akhir'); // string YYYY-MM-DD atau null

                if ($lastEnd) {
                    $lastEnd = Carbon::parse($lastEnd)->startOfDay();
                    $tanggalMulai = $lastEnd->gte($transDay) ? $lastEnd->copy()->addDay() : $transDay;
                } else {
                    $tanggalMulai = $transDay;
                }

                $tanggalAkhir = $tanggalMulai->copy()->addDays(((int) $paket->durasi) - 1);

                $metodeList = ['cash', 'transfer', 'qris'];

                $trx = TransaksiMembership::create([
                    'buyer_member_id'   => $buyerId,
                    'created_by'        => $admin->id,
                    'paket_id'          => $paket->id,
                    'tanggal_transaksi' => $tanggalTransaksi,
                    'tanggal_mulai'     => $tanggalMulai->toDateString(),
                    'tanggal_akhir'     => $tanggalAkhir->toDateString(),

                    // ENUM BARU
                    'jenis_transaksi'   => TransaksiMembership::JENIS_PEMBAYARAN, // 'pembayaran'
                    'metode_pembayaran' => $metodeList[array_rand($metodeList)],
                    'keterangan'        => 'Seeder demo',
                ]);

                // Buyer primary
                TransaksiMembershipMember::create([
                    'transaksi_membership_id' => $trx->id,
                    'member_id'               => $buyerId,
                    'role'                    => 'primary',
                ]);

                // Peserta tambahan
                foreach ($participantIds as $pid) {
                    TransaksiMembershipMember::create([
                        'transaksi_membership_id' => $trx->id,
                        'member_id'               => (int) $pid,
                        'role'                    => 'member',
                    ]);
                }

                // Tidak update tabel members (karena truth di transaksi)
            });
        }
    }
}
