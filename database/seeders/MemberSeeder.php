<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MemberSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk data members.
     *
     * Seeder ini akan:
     * - Mengambil semua user dengan role "member"
     * - Untuk tiap user, buat / update 1 baris di tabel members
     */
    public function run(): void
    {
        // Ambil semua user yang role-nya "member"
        $memberUsers = User::where('role', 'member')
            ->orderBy('id')
            ->get();

        if ($memberUsers->isEmpty()) {
            return;
        }

        foreach ($memberUsers as $index => $user) {
            // Tanggal daftar antara 1–60 hari yang lalu
            $tanggalDaftar = Carbon::today()->subDays(rand(1, 60));

            // Random: punya membership atau tidak
            $punyaMembership = (bool) rand(0, 1);

            $tanggalMulai  = null;
            $tanggalAkhir  = null;

            if ($punyaMembership) {
                // Membership mulai antara 0–10 hari setelah tanggal daftar
                $tanggalMulai = (clone $tanggalDaftar)->addDays(rand(0, 10));

                // Durasi random 7 / 14 / 30 hari
                $durasiOptions = [7, 14, 30];
                $durasiHari    = $durasiOptions[array_rand($durasiOptions)];

                // tanggal_akhir = mulai + (durasi - 1)
                $tanggalAkhir = (clone $tanggalMulai)->addDays($durasiHari - 1);
            }

            // Map jenis_kelamin ke angka:
            // 1 = Laki-laki, 2 = Perempuan
            $jkNumeric = $index % 2 === 0 ? 1 : 2;

            Member::updateOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'nama'           => $user->name ?? "Member {$index}",
                    'alamat'         => 'Jl. Contoh No. ' . ($index + 1) . ', Banjarmasin',
                    'jenis_kelamin'  => $jkNumeric,
                    'tanggal_daftar' => $tanggalDaftar->toDateString(),
                    'tanggal_mulai'  => $tanggalMulai?->toDateString(),
                    'tanggal_akhir'  => $tanggalAkhir?->toDateString(),
                    // kolom 'status', 'foto', 'qr_code_token' sengaja tidak diisi
                    // karena tidak ada di struktur tabel saat ini
                ]
            );
        }
    }
}
 