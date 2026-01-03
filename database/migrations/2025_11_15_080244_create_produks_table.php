<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->string('foto')->nullable();
            $table->string('nama');
            $table->enum('kategori', ['minuman', 'suplemen', 'lainnya'])->index();
            $table->decimal('harga', 15, 0);
            $table->unsignedBigInteger('stok')->default(0);
            $table->text('deskripsi')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Optional (kalau Anda ingin nama produk unik)
            // $table->unique('nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
