<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_produk_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaksi_produk_id')
                ->constrained('transaksi_produks')
                ->cascadeOnDelete();

            $table->foreignId('produk_id')
                ->constrained('produks')
                ->restrictOnDelete();

            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('harga_satuan');

            $table->timestamps();

            $table->index('transaksi_produk_id');
            $table->index('produk_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_produk_items');
    }
};
