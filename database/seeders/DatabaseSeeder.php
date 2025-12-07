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
        // Akun Admin
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

        // Akun Member Demo
        User::updateOrCreate(
            ['email' => 'member@sim.gym'],
            [
                'name'     => 'Member',
                'username' => 'member',
                'no_hp'    => '6304230003',
                // WAJIB: Password di-hash
                'password' => Hash::make('password'), 
                'role'     => 'member',
            ]
        );
    }
}