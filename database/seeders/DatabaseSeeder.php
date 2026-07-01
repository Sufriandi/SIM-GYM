<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==============================
        // 1. Akun ADMIN
        // ==============================
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'Admin BETA GYM',
                'email'    => 'admin@sim.gym',
                'no_hp'    => '081200009999',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // ==============================
        // 2. 5 Akun MEMBER (data realistis)
        // ==============================
        $members = [
            [
                'username'      => 'member',
                'name'          => 'Raka Pratama',
                'email'         => 'raka.pratama@sim.gym',
                'no_hp'         => '081200001111',
                'alamat'        => 'Jl. Ahmad Yani Km 6, Banjarmasin',
                'jenis_kelamin' => 'laki-laki',
            ],
            [
                'username'      => 'member2',
                'name'          => 'Siti Aisyah',
                'email'         => 'siti.aisyah@sim.gym',
                'no_hp'         => '081200001112',
                'alamat'        => 'Jl. Veteran No. 10, Banjarmasin',
                'jenis_kelamin' => 'perempuan',
            ],
            [
                'username'      => 'member3',
                'name'          => 'Farhan Akbar',
                'email'         => 'farhan.akbar@sim.gym',
                'no_hp'         => '081200001113',
                'alamat'        => 'Jl. Gatot Subroto No. 5, Banjarmasin',
                'jenis_kelamin' => 'laki-laki',
            ],
            [
                'username'      => 'member4',
                'name'          => 'Nadia Kartika',
                'email'         => 'nadia.kartika@sim.gym',
                'no_hp'         => '081200001114',
                'alamat'        => 'Jl. Pramuka No. 30, Banjarmasin',
                'jenis_kelamin' => 'perempuan',
            ],
            [
                'username'      => 'member5',
                'name'          => 'Deni Saputra',
                'email'         => 'deni.saputra@sim.gym',
                'no_hp'         => '081200001115',
                'alamat'        => 'Jl. Kayu Tangi No. 2, Banjarmasin',
                'jenis_kelamin' => 'laki-laki',
            ],
        ];

        foreach ($members as $m) {
            User::updateOrCreate(
                ['username' => $m['username']],
                [
                    'name'          => $m['name'],
                    'email'         => $m['email'],
                    'no_hp'         => $m['no_hp'],
                    'alamat'        => $m['alamat'],
                    'jenis_kelamin' => $m['jenis_kelamin'],
                    'password'      => Hash::make('password'),
                    'role'          => 'member',
                ]
            );
        }

        // ==============================
        // 3. Seeder lain (urutan penting)
        // ==============================
        $this->call([
            PaketMembershipSeeder::class,

            // Buat row members terlebih dahulu (hanya tanggal_daftar)
            MemberSeeder::class,
            ProdukSeeder::class,
            CoachSeeder::class,
            InventarisAlatSeeder::class,
        ]);
    }
}
