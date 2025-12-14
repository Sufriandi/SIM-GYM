<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_qris', function (Blueprint $table) {
            $table->id();

            $table->string('nama_qris', 150);
            $table->string('path_gambar', 255);
            $table->text('keterangan')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_qris');
    }
};
