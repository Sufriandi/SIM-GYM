<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_memberships', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // contoh: "Perhari", "1 Bulan", "Paket Couple"
            $table->enum('tipe', ['single', 'couple', 'triple']);
            $table->unsignedInteger('durasi'); // durasi dalam HARI
            $table->decimal('harga', 12, 2);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_memberships');
    }
};
