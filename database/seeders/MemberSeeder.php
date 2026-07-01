<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        // Opsional kalau benar-benar ingin bersih (gunakan saat migrate:fresh):
        // Member::truncate();

        $memberUsers = User::query()
            ->where('role', 'member')
            ->orderBy('id')
            ->get();

        if ($memberUsers->isEmpty()) {
            return;
        }

        // Tanggal daftar dibuat realistis (mundur bertahap).
        $baseDate = Carbon::now()->subDays(20)->startOfDay();

        foreach ($memberUsers as $idx => $user) {
            $payload = [
                'user_id' => $user->id,
            ];

            // Kolom-kolom opsional (dibuat aman, hanya di-set jika ada).
            if (Schema::hasColumn('members', 'tanggal_daftar')) {
                $payload['tanggal_daftar'] = (clone $baseDate)->addDays($idx)->toDateString();
            }

            if (Schema::hasColumn('members', 'tanggal_mulai')) {
                $payload['tanggal_mulai'] = null; // akan diisi oleh TransaksiMembershipSeeder
            }

            if (Schema::hasColumn('members', 'tanggal_akhir')) {
                $payload['tanggal_akhir'] = null; // akan diisi oleh TransaksiMembershipSeeder
            }

            if (Schema::hasColumn('members', 'status')) {
                $payload['status'] = 'aktif';
            }

            Member::updateOrCreate(
                ['user_id' => $user->id],
                $payload
            );
        }
    }
}
