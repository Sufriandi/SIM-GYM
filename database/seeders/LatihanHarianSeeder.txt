<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

use App\Models\LatihanHarian;
use App\Models\User;

class LatihanHarianSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil admin untuk created_by (optional)
        $adminId = User::query()
            ->where('role', 'admin')
            ->value('id');

        // Harga default dari config jika ada
        $hargaConfig = config('gym.harga_harian', [
            'umum'    => 20000,
            'pelajar' => 15000,
        ]);

        $hargaUmum    = is_array($hargaConfig) ? (int)($hargaConfig['umum'] ?? 20000) : (int) $hargaConfig;
        $hargaPelajar = is_array($hargaConfig) ? (int)($hargaConfig['pelajar'] ?? $hargaUmum) : (int) $hargaConfig;

        $namaSample = [
            'Abdul',
            'Yanto',
            'Anto',
            'Budi',
            'Rina',
            'Sinta',
            'Doni',
            'Andi',
            'Rudi',
            'Adi',
            'Fajar',
            'Riko',
            'Nia',
            'Putri',
            'Slamet',
        ];

        $keteranganSample = [
            null,
            'Latihan rutin pagi.',
            'Datang bersama teman.',
            'Menggunakan voucher promo.',
            'Free trial rekomendasi member.',
            'Latihan persiapan lomba.',
        ];

        // Support dua skema: kolom total atau harga
        $hasTotal = Schema::hasColumn('latihan_harian', 'total');
        $hasHarga = Schema::hasColumn('latihan_harian', 'harga');

        // Jika Anda sudah pakai migration versi baru (disarankan), kolom "total" harus ada.
        if (!$hasTotal && !$hasHarga) {
            // Tidak ada kolom yang cocok -> stop agar errornya jelas
            throw new \RuntimeException("Kolom 'total' atau 'harga' tidak ditemukan pada tabel latihan_harian.");
        }

        // 30 hari terakhir
        for ($i = 0; $i < 30; $i++) {
            $tanggal = Carbon::today()->subDays(29 - $i);

            $kategori = Arr::random(['umum', 'pelajar']);
            $metode   = Arr::random(['cash', 'transfer', 'qris']);

            $hargaDasar = $kategori === 'pelajar' ? $hargaPelajar : $hargaUmum;
            $hargaAkhir = $hargaDasar + Arr::random([0, 0, 0, 2000, 5000]);

            $payload = [
                'tanggal'           => $tanggal->toDateString(),
                'nama'              => Arr::random($namaSample),
                'kategori'          => $kategori,
                'metode_pembayaran' => $metode, // sekarang wajib (non-null)
                'keterangan'        => Arr::random($keteranganSample),
            ];

            // Set nilai pendapatan: total (baru) atau harga (lama)
            if ($hasTotal) {
                $payload['total'] = (int) $hargaAkhir;
            } else {
                $payload['harga'] = (int) $hargaAkhir;
            }

            // created_by bila ada kolom & admin tersedia
            if ($adminId && Schema::hasColumn('latihan_harian', 'created_by')) {
                $payload['created_by'] = (int) $adminId;
            }

            // canceled_at bila ada (optional, default null)
            if (Schema::hasColumn('latihan_harian', 'canceled_at')) {
                $payload['canceled_at'] = null;
            }

            LatihanHarian::create($payload);
        }
    }
}
