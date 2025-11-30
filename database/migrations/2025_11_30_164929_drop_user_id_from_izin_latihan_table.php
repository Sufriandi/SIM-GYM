<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin_latihan', function (Blueprint $table) {
            // 1. Hapus foreign key dulu
            // di DB kamu namanya "izin_latihan_user_id_foreign",
            // tapi di Laravel cukup pakai nama kolom seperti ini:
            $table->dropForeign(['user_id']);

            // 2. Baru hapus kolom user_id
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('izin_latihan', function (Blueprint $table) {
            // Balikin lagi kalau rollback
            $table->unsignedBigInteger('user_id')->after('id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
