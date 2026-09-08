<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. kehadiran_members: index on (tanggal, is_valid)
        if (Schema::hasTable('kehadiran_members')) {
            Schema::table('kehadiran_members', function (Blueprint $table) {
                $table->index(['tanggal', 'is_valid'], 'idx_kehadiran_tanggal_valid');
            });
        }

        // 2. izin_latihan: index on status
        if (Schema::hasTable('izin_latihan')) {
            Schema::table('izin_latihan', function (Blueprint $table) {
                $table->index('status', 'idx_izin_latihan_status');
            });
        }

        // 3. users: index on role
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('role', 'idx_users_role');
            });
        }

        // 4. members: index on deleted_at
        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                $table->index('deleted_at', 'idx_members_deleted_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kehadiran_members')) {
            Schema::table('kehadiran_members', function (Blueprint $table) {
                $table->dropIndex('idx_kehadiran_tanggal_valid');
            });
        }

        if (Schema::hasTable('izin_latihan')) {
            Schema::table('izin_latihan', function (Blueprint $table) {
                $table->dropIndex('idx_izin_latihan_status');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_role');
            });
        }

        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropIndex('idx_members_deleted_at');
            });
        }
    }
};
