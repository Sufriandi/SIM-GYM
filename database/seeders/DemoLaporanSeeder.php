<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder data demo untuk Laporan Keuangan, Laporan Kehadiran, dll.
 *
 * PENTING: pakai DB::table()->insert() (query builder), BUKAN Model::create().
 * Ini SENGAJA supaya tidak memicu Observer (TransaksiProdukObserver,
 * TransaksiMembershipObserver, KehadiranMemberObserver) yang otomatis
 * membuat Notification + push FCM setiap ada record baru.
 *
 * PENTING #2: Transaksi Membership dibuat LEBIH DULU (bukan belakangan),
 * karena Kehadiran (check-in) hanya dibuat untuk member yang punya
 * membership aktif pada tanggal tersebut - persis aturan
 * Member::hasActiveMembershipOn() yang dipakai KehadiranMemberController
 * (member.absensi.scan) untuk nge-gate absensi asli. Member yang tidak
 * pernah beli membership TIDAK akan punya riwayat check-in sama sekali,
 * konsisten dengan business rule aplikasi.
 *
 * Cakupan: 1 Juni 2026 - 8 September 2026 (hari ini), dengan tren
 * pendapatan naik tiap bulan (Juni terendah, Agustus tertinggi; September
 * lebih rendah murni karena baru 8 hari berjalan, bukan penurunan).
 *
 * Jalankan dengan:
 *   php artisan db:seed --class=DemoLaporanSeeder
 * (otomatis memanggil DemoMembersSeeder & DemoInventarisSeeder juga)
 */
class DemoLaporanSeeder extends Seeder
{
    public function run(): void
    {
        // Tambah member (9 -> ~45) dan alat inventaris dulu.
        $this->call(DemoMembersSeeder::class);
        $this->call(DemoInventarisSeeder::class);

        $memberIds = DB::table('members')->pluck('id')->all();

        if (empty($memberIds)) {
            $this->command?->warn('Tidak ada data di tabel members. Seeder dibatalkan.');
            return;
        }

        $produkList = DB::table('produks')->whereNull('deleted_at')->select('id', 'harga')->get();
        $paketList  = DB::table('paket_memberships')->where('is_public', 1)->whereNull('deleted_at')->get();
        $adminUserId = DB::table('users')->where('role', 'admin')->value('id') ?? 1;

        $start = Carbon::create(2026, 6, 1);
        $end   = Carbon::create(2026, 9, 8); // hari ini

        $namaTamu = ['Rudi', 'Sari', 'Budi', 'Wati', 'Joko', 'Ani', 'Dedi', 'Lina', 'Agus', 'Fitri', 'Hendra', 'Yuni'];
        $metodeList = ['cash', 'cash', 'cash', 'transfer', 'qris']; // cash lebih sering muncul

        DB::transaction(function () use (
            $memberIds, $produkList, $paketList, $adminUserId,
            $start, $end, $namaTamu, $metodeList
        ) {
            // =========================================================
            // 1) TRANSAKSI MEMBERSHIP DULU (dibuat lebih awal)
            //    Sambil bikin transaksinya, kita catat periode aktif tiap
            //    member ke $activePeriods supaya bisa dipakai untuk
            //    nge-gate kehadiran di langkah berikutnya.
            // =========================================================
            $activePeriods = []; // [member_id => [[start, end], ...]]

            $addPeriod = function (int $memberId, string $start, string $end) use (&$activePeriods) {
                $activePeriods[$memberId][] = [$start, $end];
            };

            $membershipCountByMonth = [6 => 3, 7 => 5, 8 => 7, 9 => 4];

            foreach ($membershipCountByMonth as $month => $count) {
                if ($paketList->isEmpty()) break;

                $daysInMonth = $month === 9 ? 8 : Carbon::create(2026, $month, 1)->daysInMonth;

                for ($i = 0; $i < $count; $i++) {
                    $tanggalTransaksi = Carbon::create(2026, $month, rand(1, $daysInMonth), rand(8, 19), rand(0, 59));

                    $paket = $paketList->random();
                    $buyer = $memberIds[array_rand($memberIds)];

                    $mulai = $tanggalTransaksi->copy()->startOfDay();
                    $akhir = $mulai->copy()->addDays(((int) $paket->durasi) - 1);

                    $noNota = 'TM' . $tanggalTransaksi->format('ymd') . strtoupper(bin2hex(random_bytes(4)));

                    $trxId = DB::table('transaksi_memberships')->insertGetId([
                        'no_nota'           => $noNota,
                        'buyer_member_id'   => $buyer,
                        'created_by'        => $adminUserId,
                        'paket_id'          => $paket->id,
                        'tanggal_transaksi' => $tanggalTransaksi,
                        'tanggal_mulai'     => $mulai->toDateString(),
                        'tanggal_akhir'     => $akhir->toDateString(),
                        'jenis_transaksi'   => 'pembayaran',
                        'metode_pembayaran' => $metodeList[array_rand($metodeList)],
                        'total'             => (int) $paket->harga,
                        'keterangan'        => null,
                        'canceled_at'       => null,
                        'created_at'        => $tanggalTransaksi,
                        'updated_at'        => $tanggalTransaksi,
                    ]);

                    DB::table('transaksi_membership_members')->insert([
                        'transaksi_membership_id' => $trxId,
                        'member_id'                => $buyer,
                        'role'                     => 'primary',
                        'tanggal_mulai'            => $mulai->toDateString(),
                        'tanggal_akhir'            => $akhir->toDateString(),
                        'created_at'               => $tanggalTransaksi,
                        'updated_at'               => $tanggalTransaksi,
                    ]);
                    $addPeriod($buyer, $mulai->toDateString(), $akhir->toDateString());

                    if (in_array($paket->tipe, ['double', 'triple'], true)) {
                        $extraCount = $paket->tipe === 'double' ? 1 : 2;
                        $others = collect($memberIds)
                            ->reject(fn ($m) => $m === $buyer)
                            ->shuffle()
                            ->take($extraCount);

                        foreach ($others as $mid) {
                            DB::table('transaksi_membership_members')->insert([
                                'transaksi_membership_id' => $trxId,
                                'member_id'                => $mid,
                                'role'                     => 'member',
                                'tanggal_mulai'            => $mulai->toDateString(),
                                'tanggal_akhir'            => $akhir->toDateString(),
                                'created_at'               => $tanggalTransaksi,
                                'updated_at'               => $tanggalTransaksi,
                            ]);
                            $addPeriod($mid, $mulai->toDateString(), $akhir->toDateString());
                        }
                    }
                }
            }

            // Helper: apakah member aktif pada tanggal tertentu?
            $isActiveOn = function (int $memberId, string $date) use (&$activePeriods) {
                foreach ($activePeriods[$memberId] ?? [] as [$s, $e]) {
                    if ($date >= $s && $date <= $e) return true;
                }
                return false;
            };

            // =========================================================
            // 2) HARIAN: Transaksi Produk, Latihan Harian, Kehadiran
            // =========================================================
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $growth = match ((int) $date->month) {
                    6 => 0.6,
                    7 => 0.8,
                    8 => 1.0,
                    default => 1.1, // September
                };
                $tanggalStr = $date->toDateString();

                // --- Transaksi Produk (kasir) - tidak terikat status membership ---
                $trxCount = max(1, (int) round(rand(1, 4) * $growth));
                for ($i = 0; $i < $trxCount; $i++) {
                    if ($produkList->isEmpty()) break;

                    $tanggalTransaksi = $date->copy()->setTime(rand(8, 20), rand(0, 59));
                    $itemCount = rand(1, 3);
                    $total = 0;
                    $items = [];

                    for ($j = 0; $j < $itemCount; $j++) {
                        $produk = $produkList->random();
                        $qty = rand(1, 3);
                        $hargaSatuan = (int) $produk->harga;
                        $total += $qty * $hargaSatuan;

                        $items[] = [
                            'produk_id'    => $produk->id,
                            'qty'          => $qty,
                            'harga_satuan' => $hargaSatuan,
                            'created_at'   => $tanggalTransaksi,
                            'updated_at'   => $tanggalTransaksi,
                        ];
                    }

                    $noNota = 'TP' . $date->format('ymd') . strtoupper(bin2hex(random_bytes(4)));
                    $buyer = (rand(0, 1) === 1) ? $memberIds[array_rand($memberIds)] : null;

                    $trxId = DB::table('transaksi_produks')->insertGetId([
                        'no_nota'           => $noNota,
                        'tanggal_transaksi' => $tanggalTransaksi,
                        'buyer_member_id'   => $buyer,
                        'created_by'        => $adminUserId,
                        'metode_pembayaran' => $metodeList[array_rand($metodeList)],
                        'total'             => $total,
                        'keterangan'        => null,
                        'canceled_at'       => null,
                        'created_at'        => $tanggalTransaksi,
                        'updated_at'        => $tanggalTransaksi,
                    ]);

                    foreach ($items as $item) {
                        $item['transaksi_produk_id'] = $trxId;
                        DB::table('transaksi_produk_items')->insert($item);
                    }
                }

                // --- Latihan Harian (walk-in umum/pelajar) ---
                // Sesuai konfirmasi: ini TIDAK terhubung ke data member sama
                // sekali (cuma input nama bebas), jadi tetap generate bebas.
                // HARGA TETAP sesuai aturan aplikasi (bukan random!):
                // Umum = Rp 20.000, Pelajar = Rp 15.000.
                $hargaTetap = ['umum' => 20000, 'pelajar' => 15000];

                $harianCount = (int) round(rand(0, 4) * $growth);
                for ($i = 0; $i < $harianCount; $i++) {
                    $waktu = $date->copy()->setTime(rand(6, 21), rand(0, 59));
                    $kategori = (rand(1, 100) <= 30) ? 'pelajar' : 'umum';
                    $harga = $hargaTetap[$kategori];

                    DB::table('latihan_harian')->insert([
                        'tanggal'           => $waktu,
                        'nama'              => $namaTamu[array_rand($namaTamu)],
                        'kategori'          => $kategori,
                        'total'             => $harga,
                        'metode_pembayaran' => $metodeList[array_rand($metodeList)],
                        'keterangan'        => null,
                        'created_by'        => $adminUserId,
                        'canceled_at'       => null,
                        'created_at'        => $waktu,
                        'updated_at'        => $waktu,
                    ]);
                }

                // --- Kehadiran Member (check-in) ---
                // HANYA member yang punya membership aktif di tanggal ini
                // yang boleh "check-in" - sesuai Member::hasActiveMembershipOn().
                $eligibleToday = array_values(array_filter(
                    $memberIds,
                    fn ($mid) => $isActiveOn($mid, $tanggalStr)
                ));

                if (!empty($eligibleToday)) {
                    // Dari yang eligible, sekitar 50-80% benar-benar datang hari itu
                    $showUpRatio = 0.5 + (0.3 * $growth / 1.1);
                    $showUpCount = max(1, (int) round(count($eligibleToday) * $showUpRatio));
                    $todaysMembers = collect($eligibleToday)->shuffle()->take($showUpCount);

                    foreach ($todaysMembers as $mid) {
                        $isValid = (rand(1, 100) <= 92) ? 1 : 0; // sebagian kecil invalid, buat demo Audit Kehadiran

                        DB::table('kehadiran_members')->insert([
                            'member_id'          => $mid,
                            'absensi_periode_id' => null,
                            'tanggal'            => $tanggalStr,
                            'jam_masuk'          => sprintf('%02d:%02d:00', rand(6, 20), rand(0, 59)),
                            'jam_keluar'         => null,
                            'device_info'        => 'Seeder Demo',
                            'ip_address'         => '127.0.0.1',
                            'is_valid'           => $isValid,
                            'created_at'         => $date,
                            'updated_at'         => $date,
                        ]);
                    }
                }
            }

            // =========================================================
            // 3) Izin Latihan / Kompensasi (biar tab Kompensasi ada isinya)
            //    Diambil dari member yang memang aktif membership-nya,
            //    biar masuk akal (ngajuin izin skip latihan = harus lagi
            //    aktif membership-nya).
            // =========================================================
            $membersWithMembership = array_keys($activePeriods);

            if (!empty($membersWithMembership)) {
                $alasanList = ['Sakit demam', 'Ada acara keluarga', 'Dinas luar kota', 'Cuti kerja'];
                $statusList = ['pending', 'disetujui', 'disetujui', 'ditolak'];

                for ($i = 0; $i < 6; $i++) {
                    $mulaiIzin = Carbon::create(2026, rand(6, 9), rand(1, 20));
                    $durasi = rand(1, 3);
                    $selesaiIzin = $mulaiIzin->copy()->addDays($durasi - 1);
                    $status = $statusList[array_rand($statusList)];

                    DB::table('izin_latihan')->insert([
                        'member_id'              => $membersWithMembership[array_rand($membersWithMembership)],
                        'tanggal_mulai'          => $mulaiIzin->toDateString(),
                        'tanggal_selesai'        => $selesaiIzin->toDateString(),
                        'jumlah_hari'            => $durasi,
                        'durasi_izin_disetujui'  => $status === 'disetujui' ? $durasi : null,
                        'keterangan_admin'       => $status === 'ditolak' ? 'Bukti tidak jelas' : null,
                        'tanggal_persetujuan'    => $status !== 'pending' ? $mulaiIzin->copy()->addDay() : null,
                        'alasan'                 => $alasanList[array_rand($alasanList)],
                        'bukti_alasan'           => null,
                        'status'                 => $status,
                        'created_at'             => $mulaiIzin,
                        'updated_at'             => $mulaiIzin,
                    ]);
                }
            }
        });

        $this->command?->info('Demo data Juni-September 2026 berhasil dibuat (kehadiran konsisten dengan status membership).');
    }
}
