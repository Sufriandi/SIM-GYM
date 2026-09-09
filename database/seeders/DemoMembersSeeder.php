<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Menambah member baru supaya total member jadi ~45 (dari yang sudah ada).
 * Tanggal daftar disebar Jan-Agustus 2026, dengan jumlah pendaftar makin
 * banyak tiap bulan (cocok sama tren omzet naik di DemoLaporanSeeder) -
 * jadi kalau nanti ada Laporan Member, datanya juga sudah mendukung
 * cerita "pertumbuhan member".
 *
 * Dipanggil otomatis dari DemoLaporanSeeder::run(), tidak perlu dijalankan
 * terpisah kecuali mau menambah member saja tanpa transaksi.
 */
class DemoMembersSeeder extends Seeder
{
    public function run(): void
    {
        $existingCount = DB::table('members')->count();
        $target = 45; // total member yang diinginkan (termasuk yang sudah ada)
        $toCreate = max(0, $target - $existingCount);

        if ($toCreate === 0) {
            $this->command?->info('Jumlah member sudah mencukupi (>= ' . $target . '), tidak menambah data baru.');
            return;
        }

        $depanList = [
            'Ahmad', 'Budi', 'Citra', 'Dewi', 'Eka', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko',
            'Kiki', 'Lestari', 'Made', 'Nia', 'Oki', 'Putri', 'Rani', 'Sinta', 'Tono', 'Umar',
            'Vina', 'Wawan', 'Yani', 'Zaki', 'Rizky', 'Dinda', 'Bayu', 'Sari', 'Andi', 'Wahyu',
            'Fitri', 'Doni', 'Ratna', 'Irfan', 'Melati', 'Galih', 'Reza', 'Intan', 'Yoga', 'Nabila',
        ];
        $belakangList = [
            'Pratama', 'Wijaya', 'Santoso', 'Kurniawan', 'Saputra', 'Anggraini', 'Ramadhan', 'Utami',
            'Setiawan', 'Handayani', 'Firmansyah', 'Lestari', 'Hidayat', 'Puspita', 'Nugroho',
            'Susanti', 'Permana', 'Wulandari', 'Yulianto', 'Safitri',
        ];

        // Distribusi pendaftar per bulan: makin banyak tiap bulan (cerita pertumbuhan)
        $distribusi = [
            1 => 1, 2 => 1, 3 => 2,   // Jan-Mar: 4 (early adopters)
            4 => 3, 5 => 3,           // Apr-Mei: 6
            6 => 8,                  // Juni: 8
            7 => 10,                 // Juli: 10
            8 => 8,                  // Agustus: 8
        ]; // total = 36 (bisa dipotong/disesuaikan otomatis sesuai $toCreate)

        $hashedPassword = Hash::make('password');
        $lastUserId = (int) (DB::table('users')->max('id') ?? 0);

        $created = 0;
        $counter = 1;

        foreach ($distribusi as $bulan => $jumlah) {
            for ($i = 0; $i < $jumlah; $i++) {
                if ($created >= $toCreate) {
                    break 2;
                }

                $depan = $depanList[array_rand($depanList)];
                $belakang = $belakangList[array_rand($belakangList)];
                $nama = "$depan $belakang";

                $uniqueSuffix = $lastUserId + $counter;
                $username = strtolower($depan) . $uniqueSuffix;
                $email = strtolower($depan) . $uniqueSuffix . '@example.com';
                $noHp = '08' . str_pad((string) (100000000 + $uniqueSuffix), 9, '0', STR_PAD_LEFT);

                $tanggalDaftar = \Carbon\Carbon::create(2026, $bulan, rand(1, 28), rand(8, 20), rand(0, 59));

                $userId = DB::table('users')->insertGetId([
                    'name'              => $nama,
                    'username'          => $username,
                    'email'             => $email,
                    'no_hp'             => $noHp,
                    'alamat'            => null,
                    'jenis_kelamin'     => rand(0, 1) === 1 ? 'laki-laki' : 'perempuan',
                    'foto'              => null,
                    'role'              => 'member',
                    'email_verified_at' => $tanggalDaftar,
                    'password'          => $hashedPassword,
                    'remember_token'    => null,
                    'deleted_at'        => null,
                    'created_at'        => $tanggalDaftar,
                    'updated_at'        => $tanggalDaftar,
                ]);

                DB::table('members')->insert([
                    'user_id'        => $userId,
                    'tanggal_daftar' => $tanggalDaftar->toDateString(),
                    'deleted_at'     => null,
                    'created_at'     => $tanggalDaftar,
                    'updated_at'     => $tanggalDaftar,
                ]);

                $created++;
                $counter++;
            }
        }

        $this->command?->info("Berhasil menambah {$created} member baru (total sekarang: " . ($existingCount + $created) . ').');
    }
}
