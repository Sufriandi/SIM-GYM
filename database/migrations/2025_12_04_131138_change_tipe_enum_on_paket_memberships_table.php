<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah definisi enum 'tipe' jadi single,double,triple
        DB::statement("
            ALTER TABLE paket_memberships
            MODIFY tipe ENUM('single','double','triple') NOT NULL
        ");
    }

    public function down(): void
    {
        // Kembalikan ke single,couple,triple jika dibutuhkan rollback
        DB::statement("
            ALTER TABLE paket_memberships
            MODIFY tipe ENUM('single','couple','triple') NOT NULL
        ");
    }
};
