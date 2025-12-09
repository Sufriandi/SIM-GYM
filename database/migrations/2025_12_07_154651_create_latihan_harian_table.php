<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('latihan_harian', function (Blueprint $table) {
            $table->id();

            $table->date('tanggal')->index();
            $table->string('nama', 100);                     // nama pelanggan
            $table->enum('kategori', ['umum', 'pelajar']);
            $table->unsignedInteger('harga');

            $table->enum('metode_pembayaran', ['cash', 'transfer', 'qris'])->nullable();
            $table->string('keterangan', 255)->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('latihan_harian');
    }
};
