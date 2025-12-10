<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; 

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ==============================
        // 1. Akun ADMIN
        // ==============================
        User::updateOrCreate(
            ['email' => 'admin@sim.gym'],
            [
                'name'     => 'Admin BETA GYM',
                'username' => 'admin',
                'no_hp'    => '6304230002',
                // WAJIB: Password di-hash
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // ==============================
        // 2. Akun MEMBER DEMO UTAMA
        //    (dipakai buat testing manual)
        // ==============================
        User::updateOrCreate(
            ['email' => 'member@sim.gym'],
            [
                'name'     => 'Member Demo',
                'username' => 'member',
                'no_hp'    => '6304230003',
                'password' => Hash::make('password'),
                'role'     => 'member',
            ]
        );

        // ==============================
        // 3. Tambahan 30 Akun Member
        //    => total akun role=member dari seeder ini = 31
        //       (1 demo di atas + 30 berikut)
        // ==============================
        for ($i = 1; $i <= 30; $i++) {
            User::updateOrCreate(
                ['email' => "member{$i}@sim.gym"],
                [
                    'name'     => "Member {$i}",
                    'username' => "member{$i}",
                    // No HP sederhana tapi unik (silakan ganti pola kalau mau)
                    'no_hp'    => '08' . str_pad((string) $i, 9, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                    'role'     => 'member',
                ]
            );
        }

        // ==============================
        // 4. Panggil Seeder Lain
        // ==============================
        $this->call([
            PaketMembershipSeeder::class,
            LatihanHarianSeeder::class,
            MemberSeeder::class,
            ProdukSeeder::class,
            CoachSeeder::class,
            InventarisAlatSeeder::class,
            IzinLatihanSeeder::class,
            KehadiranMemberSeeder::class,
            // seeder lain kalau nanti ada, tambahkan di sini juga
        ]);
    }
}
