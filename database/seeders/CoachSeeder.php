<?php

namespace Database\Seeders;

use App\Models\Coach;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CoachSeeder extends Seeder
{
    /**
     * Seed data coach (31 baris).
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // (opsional) kalau mau bersih dulu:
        // Coach::truncate();

        for ($i = 1; $i <= 31; $i++) {
            Coach::create([
                'nama'      => 'Coach ' . $faker->firstName,
                'no_hp'     => '08' . $faker->numerify('##########'),
                'alamat'    => $faker->address,
                'deskripsi' => $faker->sentence(12),
                // Wajib TIDAK null, cukup string path. 
                // Nanti kalau mau, kamu bisa bikin file gambar sesuai path ini di storage/public.
                'foto'      => 'coaches/coach-' . str_pad($i, 2, '0', STR_PAD_LEFT) . '.jpg',
            ]);
        }
    }
}
