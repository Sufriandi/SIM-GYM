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
        Schema::create('sesi_absensi', function (Blueprint $table) {
            $table->id();

            // Token QR yang akan di-encode ke QR Code
            $table->string('kode_qr', 64)->unique();

            // Nama / judul sesi absensi
            $table->string('nama_sesi', 100);

            // Tanggal + jam sesi
            $table->date('tanggal');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();

            // Status sesi
            $table->enum('status', ['aktif', 'selesai', 'nonaktif'])
                  ->default('aktif');

            // Admin pembuat sesi (FK ke users.id)
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();

            // Index tambahan untuk query laporan
            $table->index(['tanggal', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_absensi');
    }
};
