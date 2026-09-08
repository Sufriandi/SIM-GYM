<?php

namespace Database\Seeders;

use App\Models\InventarisAlat;
use Illuminate\Database\Seeder;

class InventarisAlatSeeder extends Seeder
{
    public function run(): void
    {
        // Opsional kalau benar-benar ingin bersih (gunakan saat migrate:fresh):
        // InventarisAlat::truncate();

        // Aman untuk enum kondisi: saya set semua 'baik' (sesuai seeder lama).
        $items = [
            [
                'nama'      => 'Treadmill Komersial 3.0 HP',
                'deskripsi' => 'Treadmill untuk cardio harian, cocok untuk jalan cepat hingga lari. Perawatan rutin belt dan pelumasan tiap bulan.',
                'kondisi'   => 'baik',
                'foto'      => 'inventaris/treadmill-komersial.jpg',
            ],
            [
                'nama'      => 'Bench Press Flat (Heavy Duty)',
                'deskripsi' => 'Bangku bench press rangka baja. Digunakan untuk bench press, dumbbell press, dan variasi chest workout.',
                'kondisi'   => 'baik',
                'foto'      => 'inventaris/bench-press-flat.jpg',
            ],
            [
                'nama'      => 'Set Dumbbell 2–20 kg (Pairs)',
                'deskripsi' => 'Dumbbell berpasangan untuk latihan kekuatan. Cocok untuk full-body workout dan progres beban bertahap.',
                'kondisi'   => 'baik',
                'foto'      => 'inventaris/dumbbell-set-2-20.jpg',
            ],
            [
                'nama'      => 'Sepeda Statis Magnetic Resistance',
                'deskripsi' => 'Sepeda statis untuk cardio low-impact. Cocok untuk pemula dan pemanasan sebelum latihan beban.',
                'kondisi'   => 'baik',
                'foto'      => 'inventaris/sepeda-statis-magnetic.jpg',
            ],
            [
                'nama'      => 'Cable Crossover (Dual Pulley)',
                'deskripsi' => 'Mesin kabel serbaguna untuk chest fly, triceps pushdown, face pull, dan latihan isolasi lainnya.',
                'kondisi'   => 'baik',
                'foto'      => 'inventaris/cable-crossover-dual.jpg',
            ],
        ];

        foreach ($items as $item) {
            InventarisAlat::updateOrCreate(
                ['nama' => $item['nama']], // nama alat dibuat sebagai key stabil
                $item
            );
        }
    }
}
