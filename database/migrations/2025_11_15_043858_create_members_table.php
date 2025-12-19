<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            // 1 user = 1 member
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unique('user_id');

            $table->date('tanggal_daftar')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('tanggal_daftar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
