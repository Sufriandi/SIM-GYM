<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LatihanHarian;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class LatihanHarianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil harga default dari config kalau ada
        // Misal config('gym.harga_harian') = ['umum' => 20000, 'pelajar' => 15000]
        $hargaConfig = config('gym.harga_harian', [
            'umum'    => 20000,
            'pelajar' => 15000,
        ]);

        $hargaUmum    = is_array($hargaConfig) ? ($hargaConfig['umum'] ?? 20000) : (int) $hargaConfig;
        $hargaPelajar = is_array($hargaConfig) ? ($hargaConfig['pelajar'] ?? $hargaUmum) : (int) $hargaConfig;

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

        // 30 data dengan tanggal berbeda (30 hari terakhir)
        for ($i = 0; $i < 30; $i++) {
            $tanggal = Carbon::today()->subDays(29 - $i); // mulai dari 30 hari lalu sampai hari ini

            $kategori = Arr::random(['umum', 'pelajar']);
            $metode   = Arr::random(['cash', 'transfer', 'qris']);

            // Harga dasar sesuai kategori
            $hargaDasar = $kategori === 'pelajar' ? $hargaPelajar : $hargaUmum;

            // Sedikit variasi harga (± 0–5 ribu)
            $hargaAkhir = $hargaDasar + (Arr::random([0, 0, 0, 2000, 5000]));

            LatihanHarian::create([
                'tanggal'           => $tanggal->format('Y-m-d'),
                'nama'              => Arr::random($namaSample),
                'kategori'          => $kategori,                 // 'umum' / 'pelajar'
                'harga'             => $hargaAkhir,               // integer
                'metode_pembayaran' => $metode,                   // 'cash' / 'transfer' / 'qris'
                'keterangan'        => Arr::random($keteranganSample),
            ]);
        }
    }
}
