<?php

namespace Database\Seeders;

use App\Models\Coach;
use Illuminate\Database\Seeder;

class CoachSeeder extends Seeder
{
    public function run(): void
    {
        // Opsional kalau benar-benar ingin bersih (gunakan saat migrate:fresh):
        // Coach::truncate();

        $coaches = [
            [
                'nama'      => 'Dimas Saputra',
                'no_hp'     => '081200001001',
                'alamat'    => 'Jl. Ahmad Yani Km 5, Banjarmasin',
                'deskripsi' => 'Spesialis strength training dan program pembentukan massa otot. Fokus teknik angkatan dan progres beban yang aman.',
                'foto'      => 'coaches/dimas-saputra.jpg',
            ],
            [
                'nama'      => 'Siti Rahmawati',
                'no_hp'     => '081200001002',
                'alamat'    => 'Jl. Veteran No. 18, Banjarmasin',
                'deskripsi' => 'Fokus fat loss dan latihan fungsional. Cocok untuk pemula yang ingin turunkan berat badan dengan rencana bertahap.',
                'foto'      => 'coaches/siti-rahmawati.jpg',
            ],
            [
                'nama'      => 'Farhan Maulana',
                'no_hp'     => '081200001003',
                'alamat'    => 'Jl. Gatot Subroto No. 7, Banjarmasin',
                'deskripsi' => 'Strength & conditioning untuk performa olahraga. Program peningkatan stamina, power, dan mobilitas.',
                'foto'      => 'coaches/farhan-maulana.jpg',
            ],
            [
                'nama'      => 'Nadia Putri',
                'no_hp'     => '081200001004',
                'alamat'    => 'Jl. Pramuka No. 22, Banjarmasin',
                'deskripsi' => 'Mobility & posture correction. Membantu perbaiki teknik dasar, fleksibilitas, dan mencegah cedera latihan.',
                'foto'      => 'coaches/nadia-putri.jpg',
            ],
            [
                'nama'      => 'Rizky Ananda',
                'no_hp'     => '081200001005',
                'alamat'    => 'Jl. Kayu Tangi No. 3, Banjarmasin',
                'deskripsi' => 'Body recomposition dan hypertrophy. Program split training dengan tracking progres dan edukasi nutrisi dasar.',
                'foto'      => 'coaches/rizky-ananda.jpg',
            ],
        ];

        foreach ($coaches as $coach) {
            Coach::updateOrCreate(
                ['no_hp' => $coach['no_hp']], // jadikan no_hp sebagai key yang stabil
                $coach
            );
        }
    }
}
