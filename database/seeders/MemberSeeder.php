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
        $memberUsers = User::where('role', 'member')->orderBy('id')->get();

        foreach ($memberUsers as $user) {
            $tanggalDaftar = Carbon::today()->subDays(rand(1, 60));

            Member::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'tanggal_daftar' => $tanggalDaftar->toDateString(),
                ]
            );
        }
    }
}
