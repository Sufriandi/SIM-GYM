<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_produks', function (Blueprint $table) {
            $table->id();

            // 16 char alnum, unique (generate di controller/service)
            $table->char('no_nota', 16)->unique();

            $table->dateTime('tanggal_transaksi');

            // Pembeli (member). Nullable untuk guest/non-member.
            $table->foreignId('buyer_member_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete();

            // Petugas yang input (admin/kasir)
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            // Simpan lowercase agar konsisten
            $table->enum('metode_pembayaran', ['cash', 'transfer', 'qris']);

            // Total grand total (hasil sum item: qty * harga_satuan)
            $table->unsignedBigInteger('total')->default(0);

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index('tanggal_transaksi');
            $table->index('buyer_member_id');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_produks');
    }
};
