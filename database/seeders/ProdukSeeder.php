<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ProdukSeeder extends Seeder
{
    /**
     * Seed data produk (31 baris).
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // ================================
        // 1. Cari definisi kolom 'kategori'
        // ================================
        $kategoriChoices = ['minuman']; // default minimal

        $column = DB::selectOne("SHOW COLUMNS FROM `produks` WHERE Field = 'kategori'");

        if ($column && isset($column->Type)) {
            $type = $column->Type; // contoh: enum('minuman','snack','suplement')

            // Kalau tipe-nya ENUM(..), parse nilai enum-nya
            if (str_starts_with($type, 'enum(')) {
                if (preg_match("/^enum\('(.*)'\)$/", $type, $matches)) {
                    $enumValues = explode("','", $matches[1]);
                    if (!empty($enumValues)) {
                        $kategoriChoices = $enumValues;
                    }
                }
            } else {
                // Bukan enum -> anggap varchar biasa, pakai fallback
                $kategoriChoices = [
                    'minuman',
                    'snack',
                    'suplement',
                    'merchandise',
                    'lainnya',
                ];
            }
        }

        // =================================================
        // 2. Seed 31 baris produk dengan kategori yang valid
        // =================================================
        // (opsional) hapus dulu data lama di dev, kalau mau bersih
        // DB::table('produks')->truncate();

        for ($i = 1; $i <= 31; $i++) {
            Produk::create([
                'foto'      => null, // atau 'produk/default.png' kalau punya
                'nama'      => $faker->words(2, true),     // contoh: "Isotonik Max"
                'kategori'  => $kategoriChoices[array_rand($kategoriChoices)],
                'harga'     => $faker->numberBetween(5000, 250000),
                'stok'      => $faker->numberBetween(0, 50),
                'deskripsi' => $faker->sentence(10),
            ]);
        }
    }
}
