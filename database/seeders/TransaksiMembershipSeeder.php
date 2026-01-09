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
use Illuminate\Support\Collection;

class TransaksiMembershipSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) return;

        // Demo: hanya paket public
        $pakets = PaketMembership::query()->where('is_public', true)->get();
        if ($pakets->isEmpty()) return;

        $members = Member::with('user')->get();
        if ($members->count() < 1) return;

        $metodeList = ['cash', 'transfer', 'qris'];

        // Buat tanggal transaksi yang lebih “masuk akal” (acak tapi cenderung kronologis)
        $dates = collect(range(1, 50))
            ->map(fn() => Carbon::today()->subDays(rand(0, 90))->setTime(rand(8, 20), rand(0, 59)))
            ->sort()
            ->values();

        foreach ($dates as $tanggalTransaksi) {
            DB::transaction(function () use ($admin, $pakets, $members, $metodeList, $tanggalTransaksi) {

                $paket = $pakets->random();
                $buyer = $members->random();
                $buyerId = (int) $buyer->id;

                $expectedAdditional = match ($paket->tipe) {
                    'single' => 0,
                    'double' => 1,
                    'triple' => 2,
                    default  => 0,
                };

                // Pastikan cukup member untuk additional (buyer tidak boleh ikut)
                if ($expectedAdditional > 0 && ($members->count() - 1) < $expectedAdditional) {
                    // tidak cukup member, skip transaksi ini
                    return;
                }

                // Pilih peserta tambahan secara aman dan jumlahnya HARUS tepat
                $participantIds = [];
                if ($expectedAdditional > 0) {
                    $others = $members->where('id', '!=', $buyerId)->values();

                    // random(n) -> kalau n=1 bisa return Model, jadi kita normalisasi ke Collection
                    $picked = $others->random($expectedAdditional);
                    $picked = $picked instanceof Collection ? $picked : collect([$picked]);

                    $participantIds = $picked
                        ->pluck('id')
                        ->map(fn($v) => (int) $v)
                        ->values()
                        ->all();
                }

                $allMemberIds = array_values(array_unique(array_merge([$buyerId], $participantIds)));

                $transDay = $tanggalTransaksi->copy()->startOfDay();

                // Hitung periode canonical per member
                $periods = []; // [memberId => ['mulai'=>Carbon, 'akhir'=>Carbon]]

                foreach ($allMemberIds as $mid) {
                    $mid = (int) $mid;

                    $lastEnd = TransaksiMembership::endDateTerakhirUntukMember($mid);

                    $mulai = $lastEnd
                        ? ($lastEnd->gte($transDay) ? $lastEnd->copy()->addDay() : $transDay)
                        : $transDay;

                    $akhir = $mulai->copy()->addDays(((int) $paket->durasi) - 1);

                    $periods[$mid] = ['mulai' => $mulai, 'akhir' => $akhir];
                }

                // Header MIN/MAX
                $trxMulai = collect($periods)->min(fn($p) => $p['mulai']->toDateString());
                $trxAkhir = collect($periods)->max(fn($p) => $p['akhir']->toDateString());

                // PENTING: total wajib diisi untuk pembayaran (untuk laporan keuangan)
                $trx = TransaksiMembership::create([
                    // no_nota otomatis dari Model::booted()
                    'total'             => (int) $paket->harga,

                    'buyer_member_id'   => $buyerId,
                    'created_by'        => (int) $admin->id,
                    'paket_id'          => (int) $paket->id,
                    'tanggal_transaksi' => $tanggalTransaksi,

                    'tanggal_mulai'     => $trxMulai ? Carbon::parse($trxMulai) : null,
                    'tanggal_akhir'     => $trxAkhir ? Carbon::parse($trxAkhir) : null,

                    'jenis_transaksi'   => TransaksiMembership::JENIS_PEMBAYARAN,
                    'metode_pembayaran' => $metodeList[array_rand($metodeList)],
                    'keterangan'        => 'Seeder demo',
                    'canceled_at'       => null,
                ]);

                // Pivot buyer
                TransaksiMembershipMember::create([
                    'transaksi_membership_id' => $trx->id,
                    'member_id'               => $buyerId,
                    'role'                    => 'primary',
                    'tanggal_mulai'           => $periods[$buyerId]['mulai']->toDateString(),
                    'tanggal_akhir'           => $periods[$buyerId]['akhir']->toDateString(),
                ]);

                // Pivot participants
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
