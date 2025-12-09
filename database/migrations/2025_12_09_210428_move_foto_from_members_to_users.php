<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom foto di tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('no_hp');
            // atau 'avatar', sesuaikan nama kolom yang kamu mau
        });

        // 2. (Opsional) Pindahkan data foto yang lama dari members ke users
        //    Asumsi: members.user_id mengarah ke users.id
        DB::statement('
            UPDATE users
            JOIN members ON members.user_id = users.id
            SET users.foto = members.foto
            WHERE members.foto IS NOT NULL
        ');

        // 3. Hapus kolom foto dari members
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }

    public function down(): void
    {
        // 1. Tambah lagi kolom foto di members
        Schema::table('members', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('tanggal_akhir');
        });

        // 2. (Opsional) Balikkan data dari users ke members
        DB::statement('
            UPDATE members
            JOIN users ON members.user_id = users.id
            SET members.foto = users.foto
            WHERE users.foto IS NOT NULL
        ');

        // 3. Hapus kolom foto di users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
