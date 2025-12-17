<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==============================
        // 1. Akun ADMIN
        // ==============================
        User::updateOrCreate(
            ['username' => 'admin'], // lebih stabil daripada email jika email nullable
            [
                'name'     => 'Admin BETA GYM',
                'email'    => 'admin@sim.gym', // boleh tetap isi
                'no_hp'    => '6304230002',
                'password' => 'password', // auto-hash oleh cast 'hashed'
                'role'     => 'admin',
            ]
        );

        // ==============================
        // 2. Akun MEMBER DEMO
        // ==============================
        User::updateOrCreate(
            ['username' => 'member'],
            [
                'name'          => 'Member Demo',
                'email'         => 'member@sim.gym',
                'no_hp'         => '6304230003',
                'alamat'        => 'Jl. Contoh No. 1, Banjarmasin',
                'jenis_kelamin' => 'laki-laki',
                'password'      => 'password', // auto-hash
                'role'          => 'member',
            ]
        );

        // ==============================
        // 3. Tambahan 30 Akun Member
        // ==============================
        for ($i = 1; $i <= 30; $i++) {
            User::updateOrCreate(
                ['username' => "member{$i}"],
                [
                    'name'          => "Member {$i}",
                    'email'         => "member{$i}@sim.gym",
                    'no_hp'         => '08' . str_pad((string) $i, 9, '0', STR_PAD_LEFT),
                    'alamat'        => 'Jl. Contoh No. ' . ($i + 1) . ', Banjarmasin',
                    'jenis_kelamin' => $i % 2 === 0 ? 'perempuan' : 'laki-laki',
                    'password'      => 'password',
                    'role'          => 'member',
                ]
            );
        }

        // ==============================
        // 4. Seeder lain
        // ==============================
        $this->call([
            PaketMembershipSeeder::class,
            LatihanHarianSeeder::class,

            // Set periode di tabel members (user role member sudah dibuat di atas)
            MemberSeeder::class,

            ProdukSeeder::class,
            CoachSeeder::class,
            InventarisAlatSeeder::class,
            IzinLatihanSeeder::class,
            KehadiranMemberSeeder::class,
        ]);
    }
}
