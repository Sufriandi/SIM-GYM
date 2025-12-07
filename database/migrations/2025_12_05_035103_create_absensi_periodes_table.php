<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_periodes', function (Blueprint $table) {
            $table->id();

            $table->enum('tipe_periode', ['harian', 'mingguan', 'bulanan'])
                  ->default('harian');

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            // Token unik yang akan dipakai di URL QR
            $table->string('kode_qr')->unique();

            $table->enum('status', ['aktif', 'kedaluwarsa'])
                  ->default('aktif');

            // Admin pertama yang "membuat" periode (opsional)
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_periodes');
    }
};
