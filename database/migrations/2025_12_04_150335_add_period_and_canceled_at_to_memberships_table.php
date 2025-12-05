<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom periode membership + canceled_at.
     */
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            // Periode membership per transaksi
            $table->date('tanggal_mulai')
                ->nullable()
                ->after('tanggal_transaksi');

            $table->date('tanggal_akhir')
                ->nullable()
                ->after('tanggal_mulai');

            // Jika transaksi membership dibatalkan
            $table->timestamp('canceled_at')
                ->nullable()
                ->after('keterangan');

            // (Opsional) Index untuk pencarian cepat berdasarkan periode
            $table->index('tanggal_mulai', 'memberships_tanggal_mulai_index');
            $table->index('tanggal_akhir', 'memberships_tanggal_akhir_index');
            $table->index('canceled_at', 'memberships_canceled_at_index');
        });
    }

    /**
     * Rollback perubahan.
     */
    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            // Hapus index dulu, baru kolomnya
            $table->dropIndex('memberships_tanggal_mulai_index');
            $table->dropIndex('memberships_tanggal_akhir_index');
            $table->dropIndex('memberships_canceled_at_index');

            $table->dropColumn([
                'tanggal_mulai',
                'tanggal_akhir',
                'canceled_at',
            ]);
        });
    }
};
