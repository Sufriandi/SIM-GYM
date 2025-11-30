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
        // ===================== USERS TABLE FIX =====================
        Schema::table('users', function (Blueprint $table) {

            // 1. Tambah kolom username (wajib unique)
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->after('name');
            }

            // 2. Ubah email menjadi nullable
            if (Schema::hasColumn('users', 'email')) {
                $table->string('email')->nullable()->change();
            }

            // 3. Tambah kolom no_hp nullable & unique
            if (!Schema::hasColumn('users', 'no_hp')) {
                $table->string('no_hp')->nullable()->unique()->after('email');
            }

            // 4. Pastikan role default 'member'
            if (Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('member')->change();
            }
        });


        // ===================== MEMBERS TABLE FIX =====================
        Schema::table('members', function (Blueprint $table) {

            // Kolom login tidak diperlukan lagi → hapus jika ada
            if (Schema::hasColumn('members', 'username')) {
                $table->dropColumn('username');
            }
            if (Schema::hasColumn('members', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('members', 'password')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn('members', 'no_hp')) {
                $table->dropColumn('no_hp');
            }

            // Tambah qr_code_token unik
            if (!Schema::hasColumn('members', 'qr_code_token')) {
                $table->string('qr_code_token')->nullable()->unique()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // rollback TIDAK WAJIB penuh karena ini patch structure
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) $table->dropColumn('username');
            if (Schema::hasColumn('users', 'no_hp')) $table->dropColumn('no_hp');
        });

        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'qr_code_token')) $table->dropColumn('qr_code_token');
        });
    }
};
