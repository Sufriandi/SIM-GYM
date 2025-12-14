<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $memberUsers = User::where('role', 'member')
            ->orderBy('id')
            ->get();

        if ($memberUsers->isEmpty()) {
            return;
        }

        foreach ($memberUsers as $user) {
            // tanggal_daftar antara 1–60 hari yang lalu
            $tanggalDaftar = Carbon::today()->subDays(rand(1, 60));

            // random punya membership atau tidak
            $punyaMembership = (bool) rand(0, 1);

            $tanggalMulai = null;
            $tanggalAkhir = null;

            if ($punyaMembership) {
                // mulai antara 0–10 hari setelah daftar
                $tanggalMulai = (clone $tanggalDaftar)->addDays(rand(0, 10));

                // durasi random 7/14/30 hari
                $durasiOptions = [7, 14, 30];
                $durasiHari = $durasiOptions[array_rand($durasiOptions)];

                // akhir = mulai + (durasi - 1)
                $tanggalAkhir = (clone $tanggalMulai)->addDays($durasiHari - 1);
            }

            Member::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'tanggal_daftar' => $tanggalDaftar->toDateString(),
                    'tanggal_mulai'  => $tanggalMulai?->toDateString(),
                    'tanggal_akhir'  => $tanggalAkhir?->toDateString(),
                ]
            );
        }
    }
}
