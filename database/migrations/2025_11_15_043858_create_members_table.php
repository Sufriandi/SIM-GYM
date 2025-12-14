<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            // 1 user = 1 member
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->unique('user_id');

            // tanggal daftar (default hari ini)
            $table->date('tanggal_daftar')
                ->nullable()
                ->default(DB::raw('CURRENT_DATE'));

            // periode aktif membership
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_akhir')->nullable();

            $table->timestamps();

            $table->index('tanggal_mulai');
            $table->index('tanggal_akhir');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
