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

            $table->dateTime('tanggal_transaksi')->index();

            /**
             * Header summary (ringkasan transaksi):
             * - BUKAN sumber kebenaran masa aktif individu.
             * - Akan diisi dari MIN/MAX tanggal di transaksi_membership_members.
             */
            $table->date('tanggal_mulai')->nullable()->index();
            $table->date('tanggal_akhir')->nullable()->index();

            /**
             * pembayaran  : transaksi berbayar (cash/transfer/qris) untuk aktivasi/perpanjangan membership
             * kompensasi  : penambahan masa aktif tanpa pembayaran (izin latihan/bonus hari)
             */
            $table->enum('jenis_transaksi', ['pembayaran', 'kompensasi'])
                ->default('pembayaran')
                ->index();

            // Nullable untuk kasus kompensasi (tidak ada pembayaran)
            $table->enum('metode_pembayaran', ['cash', 'transfer', 'qris'])
                ->nullable()
                ->index();

            $table->string('keterangan')->nullable();

            $table->dateTime('canceled_at')->nullable()->index();

            $table->timestamps();

            // Index untuk listing/pagination yang stabil (tanggal_transaksi + tie-breaker id)
            $table->index(['tanggal_transaksi', 'id'], 'tm_trx_date_id_idx');

            // Index ringkasan range (opsional; Anda sudah punya index individual mulai/akhir)
            $table->index(['tanggal_mulai', 'tanggal_akhir'], 'tm_trx_range_idx');

            // FK indexes (boleh tetap, tapi sebenarnya MySQL sering otomatis; tidak masalah)
            $table->index('buyer_member_id', 'tm_trx_buyer_idx');
            $table->index('created_by', 'tm_trx_creator_idx');
            $table->index('paket_id', 'tm_trx_paket_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_memberships');
    }
};
