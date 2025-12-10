<?php

namespace Database\Seeders;

use App\Models\InventarisAlat;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class InventarisAlatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Kalau mau bersih dulu:
        // InventarisAlat::truncate();

        $baseNames = [
            'Treadmill',
            'Sepeda Statis',
            'Barbel Set',
            'Dumbbell Set',
            'Kettlebell',
            'Bench Press',
            'Smith Machine',
            'Lat Pulldown',
            'Cable Crossover',
            'Leg Press',
            'Pull-up Bar',
            'Dip Station',
            'Gym Ball',
            'Matras Yoga',
            'Foam Roller',
        ];

        for ($i = 1; $i <= 31; $i++) {
            $baseName = $baseNames[array_rand($baseNames)];
            $namaAlat = $baseName . ' #' . str_pad($i, 2, '0', STR_PAD_LEFT);

            InventarisAlat::create([
                'nama'      => $namaAlat,
                'deskripsi' => $faker->sentence(15),
                // PAKAI NILAI YANG PASTI AMAN DENGAN ENUM / PANJANG KOLOM
                'kondisi'   => 'baik',
                // kolom foto tidak boleh null: isi path dummy
                'foto'      => 'inventaris/alat-' . str_pad($i, 2, '0', STR_PAD_LEFT) . '.jpg',
            ]);
        }
    }
}
