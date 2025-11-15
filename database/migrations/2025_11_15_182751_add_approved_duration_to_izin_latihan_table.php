<?php
// 2023_xx_xx_xxxxxx_add_approved_duration_to_izin_latihan_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin_latihan', function (Blueprint $table) {
            // Kolom ini menyimpan jumlah hari izin yang disetujui Admin.
            $table->unsignedSmallInteger('durasi_izin_disetujui')->nullable()->after('jumlah_hari');

            // Kolom untuk catatan/keterangan dari Admin
            $table->text('keterangan_admin')->nullable()->after('durasi_izin_disetujui');

            // Tanggal persetujuan
            $table->dateTime('tanggal_persetujuan')->nullable()->after('keterangan_admin');
        });
    }

    public function down(): void
    {
        Schema::table('izin_latihan', function (Blueprint $table) {
            $table->dropColumn(['durasi_izin_disetujui', 'keterangan_admin', 'tanggal_persetujuan']);
        });
    }
};
