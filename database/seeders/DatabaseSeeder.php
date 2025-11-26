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
        User::factory()->create([
            'name'     => 'admin',
            'email'    => 'admin@sim.gym',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Akun Member (otomatis bikin record di tabel members lewat event created di model User)
        User::factory()->create([
            'name'     => 'Member',
            'email'    => 'member@sim.gym',
            'password' => Hash::make('password'),
            'role'     => 'user',
        ]);
    }
}
