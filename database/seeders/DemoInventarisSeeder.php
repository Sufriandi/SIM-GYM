<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Menambah alat gym supaya katalog Inventaris Alat tidak cuma 5 item.
 * Foto sengaja dikosongkan (null) - tinggal upload manual lewat halaman
 * Edit Inventaris nanti setelah dapat gambarnya.
 */
class DemoInventarisSeeder extends Seeder
{
    public function run(): void
    {
        $existingNames = DB::table('inventaris_alats')->pluck('nama')->map(fn ($n) => strtolower($n))->all();

        $alatBaru = [
            ['nama' => 'Leg Press Machine', 'deskripsi' => 'Mesin untuk melatih otot kaki (quadriceps, hamstring, glutes) dengan beban bertumpuk.', 'kondisi' => 'Baik'],
            ['nama' => 'Lat Pulldown Machine', 'deskripsi' => 'Mesin untuk melatih otot punggung (lats) dengan gerakan tarikan dari atas.', 'kondisi' => 'Baik'],
            ['nama' => 'Smith Machine', 'deskripsi' => 'Mesin barbel terpandu rel vertikal, cocok untuk squat dan bench press dengan keamanan lebih.', 'kondisi' => 'Baik'],
            ['nama' => 'Rak Barbel Olympic (Set)', 'deskripsi' => 'Set barbel olympic 20kg beserta plat beban bervariasi 1.25-20kg.', 'kondisi' => 'Baik'],
            ['nama' => 'Kettlebell Set 4-24 kg', 'deskripsi' => 'Set kettlebell untuk latihan fungsional dan cardio-strength.', 'kondisi' => 'Baik'],
            ['nama' => 'Rowing Machine', 'deskripsi' => 'Mesin dayung untuk latihan cardio full-body low-impact.', 'kondisi' => 'Baik'],
            ['nama' => 'Elliptical Trainer', 'deskripsi' => 'Alat cardio low-impact yang menggerakkan lengan dan kaki bersamaan.', 'kondisi' => 'Baik'],
            ['nama' => 'Preacher Curl Bench', 'deskripsi' => 'Bangku khusus untuk isolasi otot bisep saat curl.', 'kondisi' => 'Maintenance'],
            ['nama' => 'Punching Bag', 'deskripsi' => 'Samsak tinju untuk latihan kardio dan teknik pukulan.', 'kondisi' => 'Baik'],
            ['nama' => 'Matras Yoga (Set 10 pcs)', 'deskripsi' => 'Matras untuk kelas yoga, stretching, dan latihan lantai.', 'kondisi' => 'Baik'],
            ['nama' => 'Resistance Band Set', 'deskripsi' => 'Set resistance band berbagai tingkat resistensi untuk latihan fungsional.', 'kondisi' => 'Baik'],
            ['nama' => 'Ab Roller & Sit Up Bench', 'deskripsi' => 'Peralatan untuk latihan otot core dan perut.', 'kondisi' => 'Rusak'],
        ];

        $now = now();
        $inserted = 0;

        foreach ($alatBaru as $alat) {
            if (in_array(strtolower($alat['nama']), $existingNames, true)) {
                continue; // sudah ada, skip biar tidak duplikat
            }

            DB::table('inventaris_alats')->insert([
                'nama'       => $alat['nama'],
                'foto'       => null, // upload manual nanti
                'deskripsi'  => $alat['deskripsi'],
                'kondisi'    => $alat['kondisi'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $inserted++;
        }

        $this->command?->info("Berhasil menambah {$inserted} alat baru ke Inventaris Alat.");
    }
}
