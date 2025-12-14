<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_rekening', function (Blueprint $table) {
            $table->id();

            $table->string('nama_bank', 100);
            $table->string('nomor_rekening', 50);
            $table->string('nama_pemilik', 150);

            $table->timestamps();
            $table->softDeletes();

            // Opsional: cegah duplikasi
            // $table->unique(['nama_bank', 'nomor_rekening'], 'info_rekening_unique_bank_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_rekening');
    }
};
