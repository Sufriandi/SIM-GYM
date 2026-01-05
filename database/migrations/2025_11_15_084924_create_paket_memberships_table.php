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

            $table->string('nama', 100);
            $table->enum('tipe', ['single', 'double', 'triple']); // final
            $table->unsignedInteger('durasi'); // hari

            // harga dalam rupiah tanpa desimal (disarankan)
            $table->unsignedBigInteger('harga');

            $table->string('deskripsi', 255)->nullable();

            // Paket yang tampil & bisa dibeli oleh member (marketplace)
            $table->boolean('is_public')->default(true)->index();

            $table->softDeletes(); // agar paket bisa dinonaktifkan
            $table->timestamps();

            $table->index(['tipe', 'durasi'], 'pm_tipe_durasi_idx');
            $table->index(['is_public', 'tipe'], 'pm_public_tipe_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_memberships');
    }
};
