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
        Schema::table('penjualan_produks', function (Blueprint $table) {
            $table->foreignId('member_id')
                  ->nullable() // Karena di skema awal Anda member_id bersifat opsional/nullable
                  ->constrained('users') // Biasanya 'members' adalah tabel 'users' di Laravel
                  ->nullOnDelete() // Jika member dihapus, member_id di transaksi ini disetel menjadi NULL
                  ->after('produk_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan_produks', function (Blueprint $table) {
            // Hapus foreign key constraint
            // Format penamaan constraint default Laravel adalah: {table}_{column}_foreign
            $table->dropConstrainedForeignId('member_id'); 
        });
    }
};