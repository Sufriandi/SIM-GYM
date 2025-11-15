<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

         // Akun Admin
        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@sim.gym',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        // Akun User Biasa
        User::factory()->create([
            'name' => 'Member',
            'email' => 'member@sim.gym',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }
}
