<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('body');

            // target: all (broadcast) atau user (khusus 1 user)
            $table->enum('target', ['all', 'user'])->default('all');
            $table->unsignedBigInteger('target_user_id')->nullable();

            // optional metadata
            $table->string('type')->nullable(); // misal: promo, info, membership
            $table->json('data')->nullable();   // payload tambahan

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['target', 'target_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
