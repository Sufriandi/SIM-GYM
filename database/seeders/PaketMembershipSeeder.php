<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaketMembership;

class PaketMembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pakets = [
            // === SINGLE REGULER ===
            [
                'nama'      => 'Paket 1 Bulan',
                'tipe'      => 'single',      // enum: single
                'durasi'    => 30,           // hari
                'harga'     => 250000,
                'deskripsi' => 'Membership 1 bulan reguler.',
            ],

            // === SINGLE PELAJAR ===
            [
                'nama'      => 'Paket 1 Bulan Pelajar',
                'tipe'      => 'single',
                'durasi'    => 30,
                'harga'     => 200000,
                'deskripsi' => 'Khusus pelajar, wajib menunjukkan kartu pelajar.',
            ],

            // === SINGLE MULTI BULAN ===
            [
                'nama'      => 'Paket 2 Bulan',
                'tipe'      => 'single',
                'durasi'    => 60,
                'harga'     => 450000,
                'deskripsi' => 'Membership 2 bulan.',
            ],
            [
                'nama'      => 'Paket 3 Bulan',
                'tipe'      => 'single',
                'durasi'    => 90,
                'harga'     => 600000,
                'deskripsi' => 'Membership 3 bulan.',
            ],
            [
                'nama'      => 'Paket 6 Bulan',
                'tipe'      => 'single',
                'durasi'    => 180,
                'harga'     => 1100000,
                'deskripsi' => 'Membership 6 bulan.',
            ],
            [
                'nama'      => 'Paket 1 Tahun',
                'tipe'      => 'single',
                'durasi'    => 365,
                'harga'     => 2100000,
                'deskripsi' => 'Membership 1 tahun.',
            ],

            // === COUPLE (DOUBLE) ===
            [
                'nama'      => 'Paket Couple 1 Bulan',
                'tipe'      => 'double',
                'durasi'    => 30,
                'harga'     => 450000,
                'deskripsi' => 'Paket couple untuk 2 orang, 1 bulan.',
            ],

            // === TRIPLE ===
            [
                'nama'      => 'Paket Triple 1 Bulan',
                'tipe'      => 'triple',
                'durasi'    => 30,
                'harga'     => 600000,
                'deskripsi' => 'Paket triple untuk 3 orang, 1 bulan.',
            ],
        ];

        foreach ($pakets as $paket) {
            // supaya kalau seeder dijalankan ulang, data tidak dobel
            PaketMembership::updateOrCreate(
                ['nama' => $paket['nama']],
                $paket
            );
        }
    }
}
