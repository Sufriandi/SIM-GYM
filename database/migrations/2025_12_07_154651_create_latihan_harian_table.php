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

            // Samakan gaya dengan transaksi lain (datetime)
            $table->dateTime('tanggal')->index();

            $table->string('nama', 100);

            $table->enum('kategori', ['umum', 'pelajar'])->index();

            // Samakan nama kolom pendapatan: total
            $table->unsignedBigInteger('total')->default(0);

            // Sesuai keputusan: selalu ada (tidak nullable)
            $table->enum('metode_pembayaran', ['cash', 'transfer', 'qris'])->index();

            $table->string('keterangan', 255)->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->index();

            // Optional tapi sangat berguna untuk audit/void seperti transaksi lain
            $table->dateTime('canceled_at')->nullable()->index();

            $table->timestamps();

            // Listing stabil (optional)
            $table->index(['tanggal', 'id'], 'lh_date_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('latihan_harian');
    }
};
