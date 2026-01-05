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

        // Untuk demo transaksi "pembayaran", ambil paket yang public saja
        $pakets = PaketMembership::query()->where('is_public', true)->get();
        if ($pakets->isEmpty()) return;

        $members = Member::with('user')->get();
        if ($members->count() < 1) return;

        $metodeList = ['cash', 'transfer', 'qris'];

        for ($i = 0; $i < 50; $i++) {
            DB::transaction(function () use ($admin, $pakets, $members, $metodeList) {

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

                // Pilih peserta tambahan (tidak boleh sama dengan buyer)
                $participantIds = [];
                if ($maxAdditional > 0 && $members->count() > 1) {
                    $others = $members->where('id', '!=', $buyerId);

                    $picked = $others->random(min($maxAdditional, $others->count()));

                    $participantIds = collect($picked)
                        ->pluck('id')
                        ->map(fn($v) => (int) $v)
                        ->unique()
                        ->values()
                        ->all();
                }

                $allMemberIds = array_values(array_unique(array_merge([$buyerId], $participantIds)));

                $tanggalTransaksi = Carbon::today()
                    ->subDays(rand(0, 90))
                    ->setTime(rand(8, 20), rand(0, 59));

                $transDay = $tanggalTransaksi->copy()->startOfDay();

                // Hitung periode canonical per member (mengikuti logika controller baru)
                $periods = []; // [memberId => ['mulai'=>Carbon, 'akhir'=>Carbon]]

                foreach ($allMemberIds as $mid) {
                    $mid = (int) $mid;

                    // Ambil end-date terakhir per member (pivot-based, transaksi valid)
                    $lastEnd = TransaksiMembership::endDateTerakhirUntukMember($mid);

                    $mulai = $lastEnd
                        ? ($lastEnd->gte($transDay) ? $lastEnd->copy()->addDay() : $transDay)
                        : $transDay;

                    $akhir = $mulai->copy()->addDays(((int) $paket->durasi) - 1);

                    $periods[$mid] = ['mulai' => $mulai, 'akhir' => $akhir];
                }

                // Header summary MIN/MAX
                $trxMulai = collect($periods)->min(fn($p) => $p['mulai']->toDateString());
                $trxAkhir = collect($periods)->max(fn($p) => $p['akhir']->toDateString());

                $trx = TransaksiMembership::create([
                    'buyer_member_id'   => $buyerId,
                    'created_by'        => $admin->id,
                    'paket_id'          => $paket->id,
                    'tanggal_transaksi' => $tanggalTransaksi,

                    // summary (bukan sumber kebenaran per member)
                    'tanggal_mulai'     => $trxMulai ? Carbon::parse($trxMulai)->toDateString() : null,
                    'tanggal_akhir'     => $trxAkhir ? Carbon::parse($trxAkhir)->toDateString() : null,

                    'jenis_transaksi'   => TransaksiMembership::JENIS_PEMBAYARAN,
                    'metode_pembayaran' => $metodeList[array_rand($metodeList)],
                    'keterangan'        => 'Seeder demo',
                ]);

                // Buyer primary (pivot canonical)
                TransaksiMembershipMember::create([
                    'transaksi_membership_id' => $trx->id,
                    'member_id'               => $buyerId,
                    'role'                    => 'primary',
                    'tanggal_mulai'           => $periods[$buyerId]['mulai']->toDateString(),
                    'tanggal_akhir'           => $periods[$buyerId]['akhir']->toDateString(),
                ]);

                // Peserta tambahan (pivot canonical)
                foreach ($participantIds as $pid) {
                    $pid = (int) $pid;

                    TransaksiMembershipMember::create([
                        'transaksi_membership_id' => $trx->id,
                        'member_id'               => $pid,
                        'role'                    => 'member',
                        'tanggal_mulai'           => $periods[$pid]['mulai']->toDateString(),
                        'tanggal_akhir'           => $periods[$pid]['akhir']->toDateString(),
                    ]);
                }
            });
        }
    }
}
