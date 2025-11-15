<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_produk', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke tabel produks
            $table->foreignId('produk_id')
                  ->constrained('produks')
                  ->cascadeOnDelete();

            $table->bigInteger('jumlah');
            $table->date('tanggal')->index();
            $table->string('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_produk');
    }
};
