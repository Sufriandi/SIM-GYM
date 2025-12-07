<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kehadiran_members', function (Blueprint $table) {
            $table->id();

            // Relasi ke member
            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete();

            // Relasi ke periode absensi (opsional bisa null)
            $table->foreignId('absensi_periode_id')
                ->nullable()
                ->constrained('absensi_periodes')
                ->nullOnDelete();

            // Tanggal & jam
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();

            // Info device & IP (opsional, untuk audit)
            $table->string('device_info')->nullable();
            $table->string('ip_address', 45)->nullable(); // IPv4/IPv6

            // Validasi (misalnya kalau nanti ada deteksi kecurangan)
            $table->boolean('is_valid')->default(true);

            $table->timestamps();

            // Biar satu member hanya sekali per hari di satu periode (opsional)
            $table->unique(['member_id', 'tanggal', 'absensi_periode_id'], 'uniq_member_tanggal_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kehadiran_members');
    }
};
