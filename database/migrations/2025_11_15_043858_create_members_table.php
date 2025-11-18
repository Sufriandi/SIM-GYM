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
        Schema::create('members', function (Blueprint $table) {
          $table->id();

          /**
           * profil singkat member
           */
            $table->string('nama');
            $table->string('username')->unique(); // username unik
            $table->string('email')->unique();
            $table->string('password');

            /**
             * opsional
             */
            $table->string('no_hp')->nullable()->unique();
            $table->text('alamat')->nullable();


          /**
           * Otomatis create tanggal_daftar waktu sekarang
           */
            $table->date('tanggal_daftar')->nullable();

            /**
             * Penting, Inti dari masa aktif membership
             */
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_akhir')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('nonaktif');


            $table->string('foto')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
