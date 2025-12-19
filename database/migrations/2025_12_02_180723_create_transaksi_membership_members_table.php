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

            $table->timestamps();

            // Nama constraint dipendekkan agar tidak lewat batas MySQL
            $table->unique(
                ['transaksi_membership_id', 'member_id'],
                'tm_members_txid_member_unique'
            );

            $table->index(['member_id', 'role'], 'tm_members_member_role_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_membership_members');
    }
};
