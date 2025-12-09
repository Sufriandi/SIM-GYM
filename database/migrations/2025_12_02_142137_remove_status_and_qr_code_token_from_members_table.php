<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            //Menghapus kolom status
            if (schema::hasColumn('members', 'status')) {
                $table->dropColumn('status');
            }
            // Hapus kolom qr_code_token
            if (Schema::hasColumn('members', 'qr_code_token')) {
                $table->dropColumn('qr_code_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Kembalikan kolom status
            $table->enum('status', ['aktif', 'nonaktif'])
                ->default('nonaktif')
                ->after('tanggal_akhir');

            // Kembalikan kolom qr_code_token
            $table->string('qr_code_token')
                ->nullable()
                ->unique()
                ->after('status');
        });
    }
};
