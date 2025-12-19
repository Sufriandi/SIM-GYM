<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KehadiranMemberSeeder extends Seeder
{
    public function run(): void
    {
        // ====== Konfigurasi ======
        $minPerDay = 10;
        $maxPerDay = 20;

        // 3 bulan terakhir sampai hari ini
        $end   = Carbon::today()->endOfDay();
        $start = Carbon::today()->subMonthsNoOverflow(3)->startOfDay();

        // Jam operasional (random)
        $openHour  = 6;   // 06:00
        $closeHour = 22;  // 22:59

        // ====== Validasi dasar ======
        if (!Schema::hasTable('kehadiran_members')) {
            $this->command?->warn("Tabel kehadiran_members tidak ditemukan. Seeder dibatalkan.");
            return;
        }

        $memberIds = DB::table('members')->pluck('id')->all();
        $memberCount = count($memberIds);

        if ($memberCount === 0) {
            $this->command?->warn("Tidak ada data members. Buat member dulu, baru jalankan seeder absensi.");
            return;
        }

        // ====== Deteksi kolom (biar sesuai DB terbaru Anda) ======
        $hasTanggal   = Schema::hasColumn('kehadiran_members', 'tanggal');
        $hasJamMasuk  = Schema::hasColumn('kehadiran_members', 'jam_masuk');
        $hasPeriodeId = Schema::hasColumn('kehadiran_members', 'absensi_periode_id');
        $hasCreatedAt = Schema::hasColumn('kehadiran_members', 'created_at');
        $hasUpdatedAt = Schema::hasColumn('kehadiran_members', 'updated_at');

        // Opsional: jika tabel absensi_periodes ada, kita coba “pasangkan” periode aktif yang sesuai tanggal
        $periodes = collect();
        if ($hasPeriodeId && Schema::hasTable('absensi_periodes')) {
            $periodes = DB::table('absensi_periodes')
                ->select('id', 'status', 'tanggal_mulai', 'tanggal_selesai')
                ->get();
        }

        // Agar seeder bisa di-run berulang tanpa menumpuk (opsional).
        // Hapus data di range 3 bulan terakhir saja.
        if ($hasCreatedAt) {
            DB::table('kehadiran_members')
                ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                ->delete();
        }

        // ====== Generate data ======
        $batch = [];
        $batchLimit = 1000;

        $cursorDate = $start->copy()->startOfDay();
        while ($cursorDate->lte($end)) {
            $target = random_int($minPerDay, $maxPerDay);
            $target = min($target, $memberCount);

            // Ambil subset member acak tanpa duplikat (1 record per member per hari)
            $pool = $memberIds;
            shuffle($pool);
            $picked = array_slice($pool, 0, $target);

            // Tentukan periode (jika tersedia)
            $periodeIdForDay = null;
            if ($hasPeriodeId && $periodes->isNotEmpty()) {
                $periode = $periodes->first(function ($p) use ($cursorDate) {
                    if (empty($p->tanggal_mulai) || empty($p->tanggal_selesai)) return false;
                    $d = $cursorDate->toDateString();
                    return $p->status === 'aktif'
                        && $d >= Carbon::parse($p->tanggal_mulai)->toDateString()
                        && $d <= Carbon::parse($p->tanggal_selesai)->toDateString();
                });

                $periodeIdForDay = $periode?->id;
            }

            foreach ($picked as $memberId) {
                // Random waktu check-in
                $h = random_int($openHour, $closeHour);
                $m = random_int(0, 59);
                $s = random_int(0, 59);

                $created = $cursorDate->copy()->setTime($h, $m, $s);

                $row = [
                    'member_id' => $memberId,
                ];

                if ($hasPeriodeId) {
                    // Boleh null, sesuai sistem Anda yang kadang pakai absensi_periode_id, kadang null
                    $row['absensi_periode_id'] = $periodeIdForDay;
                }

                if ($hasTanggal) {
                    // Simpan tanggal (date) kalau kolom ada
                    $row['tanggal'] = $cursorDate->toDateString();
                }

                if ($hasJamMasuk) {
                    // Simpan jam masuk (time) kalau kolom ada
                    $row['jam_masuk'] = $created->format('H:i:s');
                }

                if ($hasCreatedAt) {
                    $row['created_at'] = $created->toDateTimeString();
                }

                if ($hasUpdatedAt) {
                    $row['updated_at'] = $created->toDateTimeString();
                }

                $batch[] = $row;

                if (count($batch) >= $batchLimit) {
                    DB::table('kehadiran_members')->insert($batch);
                    $batch = [];
                }
            }

            $cursorDate->addDay();
        }

        if (!empty($batch)) {
            DB::table('kehadiran_members')->insert($batch);
        }

        $this->command?->info("Seeder absensi selesai: {$start->toDateString()} s/d {$end->toDateString()} (min {$minPerDay}, max {$maxPerDay} per hari).");
    }
}
