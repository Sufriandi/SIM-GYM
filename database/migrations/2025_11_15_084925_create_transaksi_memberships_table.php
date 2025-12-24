<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_memberships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('buyer_member_id')
                ->constrained('members')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('paket_id')
                ->constrained('paket_memberships')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->dateTime('tanggal_transaksi');

            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir');

            /**
             * pembayaran  : transaksi berbayar (cash/transfer/qris) untuk aktivasi/perpanjangan membership
             * kompensasi  : penambahan masa aktif tanpa pembayaran (izin latihan/bonus hari)
             */
            $table->enum('jenis_transaksi', ['pembayaran', 'kompensasi'])
                ->default('pembayaran');

            // Nullable untuk kasus kompensasi (tidak ada pembayaran)
            $table->enum('metode_pembayaran', ['cash', 'transfer', 'qris'])
                ->nullable();

            $table->string('keterangan')->nullable();
            $table->dateTime('canceled_at')->nullable();

            $table->timestamps();

            $table->index('tanggal_transaksi');
            $table->index(['tanggal_mulai', 'tanggal_akhir']);
            $table->index('buyer_member_id');
            $table->index('created_by');
            $table->index('paket_id');
            $table->index('canceled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_memberships');
    }
};
