<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id')->unique();
            $table->timestamps();

            $table->foreign('member_id')
                ->references('id')->on('members')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_carts');
    }
};
