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

            $table->char('no_nota', 16)->unique();
            $table->dateTime('tanggal_transaksi')->index();

            // Pembeli (member). Nullable untuk guest/non-member.
            $table->foreignId('buyer_member_id')
                ->nullable()
                ->index() // <-- PINDAH KE SINI
                ->constrained('members')
                ->nullOnDelete();

            // Petugas yang input (admin/kasir)
            $table->foreignId('created_by')
                ->index() // <-- PINDAH KE SINI
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('metode_pembayaran', ['cash', 'transfer', 'qris'])->index();

            $table->unsignedBigInteger('total')->default(0);

            $table->text('keterangan')->nullable();

            $table->dateTime('canceled_at')->nullable()->index();

            $table->timestamps();

            $table->index(['tanggal_transaksi', 'id'], 'tp_trx_date_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_produks');
    }
};
