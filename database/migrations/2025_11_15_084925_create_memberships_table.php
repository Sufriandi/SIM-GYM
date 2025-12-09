<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();

            // Member utama
            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Paket membership yang dibeli (FULL FK)
            $table->foreignId('paket_id')
                ->constrained('paket_memberships')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Info transaksi
            $table->dateTime('tanggal_transaksi');
            $table->enum('metode_pembayaran', ['cash', 'transfer', 'qris'])->default('cash');
            $table->string('keterangan')->nullable();

            $table->timestamps();

            // Optional index
            $table->index('tanggal_transaksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
