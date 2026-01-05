<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaketMembership;

class PaketMembershipSeeder extends Seeder
{
    public function run(): void
    {
        $pakets = [
            // =========================
            // Paket PUBLIC (tampil di member)
            // =========================
            [
                'nama' => 'Paket 1 Bulan',
                'tipe' => 'single',
                'durasi' => 30,
                'harga' => 250000,
                'deskripsi' => 'Membership reguler 1 bulan.',
                'is_public' => true,
            ],
            [
                'nama' => 'Paket 1 Bulan Pelajar',
                'tipe' => 'single',
                'durasi' => 30,
                'harga' => 200000,
                'deskripsi' => 'Khusus pelajar; wajib menunjukkan kartu pelajar.',
                'is_public' => true,
            ],
            [
                'nama' => 'Paket 2 Bulan',
                'tipe' => 'single',
                'durasi' => 60,
                'harga' => 450000,
                'deskripsi' => 'Membership reguler 2 bulan.',
                'is_public' => true,
            ],
            [
                'nama' => 'Paket 3 Bulan',
                'tipe' => 'single',
                'durasi' => 90,
                'harga' => 600000,
                'deskripsi' => 'Membership reguler 3 bulan.',
                'is_public' => true,
            ],
            [
                'nama' => 'Paket 6 Bulan',
                'tipe' => 'single',
                'durasi' => 180,
                'harga' => 1100000,
                'deskripsi' => 'Membership reguler 6 bulan.',
                'is_public' => true,
            ],
            [
                'nama' => 'Paket 1 Tahun',
                'tipe' => 'single',
                'durasi' => 365,
                'harga' => 2100000,
                'deskripsi' => 'Membership reguler 1 tahun.',
                'is_public' => true,
            ],
            [
                'nama' => 'Paket Couple 1 Bulan',
                'tipe' => 'double',
                'durasi' => 30,
                'harga' => 450000,
                'deskripsi' => 'Paket untuk 2 orang selama 1 bulan.',
                'is_public' => true,
            ],
            [
                'nama' => 'Paket Triple 1 Bulan',
                'tipe' => 'triple',
                'durasi' => 30,
                'harga' => 600000,
                'deskripsi' => 'Paket untuk 3 orang selama 1 bulan.',
                'is_public' => true,
            ],

            // =========================
            // Paket INTERNAL (tidak tampil di member)
            // =========================
            [
                'nama' => 'Paket Trial 1',
                'tipe' => 'single',
                // durasi di paket ini tidak terlalu penting karena trial diatur via jumlah_hari pada kompensasi
                // tapi tetap wajib terisi sesuai schema
                'durasi' => 1,
                'harga' => 0,
                'deskripsi' => 'INTERNAL: Paket trial/bonus. Tidak tampil di aplikasi member. Durasi efektif diatur oleh admin.',
                'is_public' => false,
            ],
        ];

        foreach ($pakets as $paket) {
            // Kunci updateOrCreate dibuat lebih stabil (nama+tipe) untuk mengurangi risiko bentrok.
            PaketMembership::updateOrCreate(
                [
                    'nama' => $paket['nama'],
                    'tipe' => $paket['tipe'],
                ],
                $paket
            );
        }
    }
}
