<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_membership_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaksi_membership_id')
                ->constrained('transaksi_memberships')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('role', ['primary', 'member'])->default('member');

            // Canonical per-member period (sumber kebenaran masa aktif individu)
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir');

            $table->timestamps();

            // Nama constraint dipendekkan agar tidak lewat batas MySQL
            $table->unique(
                ['transaksi_membership_id', 'member_id'],
                'tm_members_txid_member_unique'
            );

            // Untuk kebutuhan filtering role (admin) & menjaga query yang sudah ada tetap cepat
            $table->index(['member_id', 'role'], 'tm_members_member_role_idx');

            // Index penting untuk performa:
            // - cari end-date terakhir untuk member
            $table->index(['member_id', 'tanggal_akhir'], 'tm_members_member_end_idx');

            // - cek aktif pada tanggal tertentu (range). MySQL tetap akan terbantu dari member_id + salah satu tanggal.
            $table->index(['member_id', 'tanggal_mulai'], 'tm_members_member_start_idx');

            // - untuk agregasi cepat per transaksi (MIN/MAX tanggal per transaksi) atau render detail
            $table->index(['transaksi_membership_id', 'tanggal_mulai'], 'tm_members_txid_start_idx');
            $table->index(['transaksi_membership_id', 'tanggal_akhir'], 'tm_members_txid_end_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_membership_members');
    }
};
