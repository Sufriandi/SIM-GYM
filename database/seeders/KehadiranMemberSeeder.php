<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KehadiranMember;
use App\Models\Member;
use Carbon\Carbon;
use Faker\Factory as Faker;

class KehadiranMemberSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $memberIds = Member::pluck('id');

        if ($memberIds->isEmpty()) {
            return;
        }

        // KehadiranMember::truncate(); // opsional

        for ($i = 1; $i <= 31; $i++) {
            $memberId = $memberIds->random();

            // tanggal dalam 30 hari terakhir
            $tanggal = Carbon::today()->subDays(rand(0, 30));

            // jam masuk antara 06:00 - 21:00
            $jamMasuk = Carbon::createFromTime(rand(6, 21), rand(0, 59));
            // jam keluar 1–3 jam setelah jam masuk
            $jamKeluar = (clone $jamMasuk)->addHours(rand(1, 3));

            KehadiranMember::create([
                'member_id'          => $memberId,
                'absensi_periode_id' => null, // kalau nanti pakai periode, bisa diisi dari seeder lain
                'tanggal'            => $tanggal->toDateString(),
                'jam_masuk'          => $jamMasuk->format('H:i:s'),
                'jam_keluar'         => $jamKeluar->format('H:i:s'),
                'ip_address'         => $faker->ipv4(),
                'device_info'        => $faker->randomElement(['Android App', 'iOS App', 'Web Browser']),
                'is_valid'           => $faker->boolean(90), // 90% valid
            ]);
        }
    }
}
