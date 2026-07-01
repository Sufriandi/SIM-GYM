<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IzinLatihan;
use App\Models\Member;
use Carbon\Carbon;
use Faker\Factory as Faker;

class IzinLatihanSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $memberIds = Member::pluck('id');

        if ($memberIds->isEmpty()) {
            return; // tidak ada member, skip
        }

        // IzinLatihan::truncate(); // hati-hati kalau ada relasi

        $statusList = ['pending', 'disetujui', 'ditolak'];

        for ($i = 1; $i <= 31; $i++) {
            $memberId = $memberIds->random();

            // tanggal mulai dalam 60 hari ke belakang s/d 15 hari ke depan
            $startOffset = rand(-60, 15);
            $tanggalMulai = Carbon::today()->addDays($startOffset);
            $jumlahHari = rand(1, 7);
            $tanggalSelesai = (clone $tanggalMulai)->addDays($jumlahHari - 1);

            $status = $faker->randomElement($statusList);

            $durasiDisetujui = null;
            $keteranganAdmin = null;
            $tanggalPersetujuan = null;

            if ($status === 'disetujui') {
                $durasiDisetujui = $jumlahHari; // anggap disetujui full
                $keteranganAdmin = $faker->sentence(6);
                $tanggalPersetujuan = (clone $tanggalMulai)->subDays(rand(0, 3));
            } elseif ($status === 'ditolak') {
                $durasiDisetujui = 0;
                $keteranganAdmin = 'Pengajuan ditolak: ' . $faker->sentence(4);
                $tanggalPersetujuan = (clone $tanggalMulai)->subDays(rand(0, 3));
            }

            IzinLatihan::create([
                'member_id'             => $memberId,
                'tanggal_mulai'         => $tanggalMulai->toDateString(),
                'tanggal_selesai'       => $tanggalSelesai->toDateString(),
                'jumlah_hari'           => $jumlahHari,
                'alasan'                => $faker->sentence(10),
                'bukti_alasan'          => null,
                'status'                => $status,
                'durasi_izin_disetujui' => $durasiDisetujui,
                'keterangan_admin'      => $keteranganAdmin,
                'tanggal_persetujuan'   => $tanggalPersetujuan,
            ]);
        }
    }
}
