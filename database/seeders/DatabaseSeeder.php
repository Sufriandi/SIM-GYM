<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@sim.gym'], // key untuk pencarian
            [
                'name'     => 'Admin BETA GYM',
                'username' => 'admin',          // jelas & gampang diingat
                'no_hp'    => '6304230002',   // optional, bisa dipakai login juga
                'password' => 'password',       // akan di-hash otomatis oleh cast
                'role'     => 'admin',
            ]
        );

        // Akun Member Demo
        User::updateOrCreate(
            ['email' => 'member@sim.gym'],
            [
                'name'     => 'Member',
                'username' => 'member',
                'no_hp'    => '6304230003',
                'password' => 'password',       // plain text, di-hash oleh model
                'role'     => 'member',
            ]
        );

        $this->call([
            PaketMembershipSeeder::class,
            // seeder lain kalau nanti ada, tambahkan di sini juga
        ]);
    }
}
