<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan_produks', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke tabel produks
            $table->foreignId('produk_id')
                  ->constrained('produks')
                  ->cascadeOnDelete();

            $table->integer('jumlah');
            $table->decimal('total_harga', 15, 2);
            $table->enum('metode_pembayaran', ["cash", "qris", "transfer"]);
            $table->string('keterangan')->nullable();
            $table->dateTime('tanggal_transaksi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_produks');
    }
};
