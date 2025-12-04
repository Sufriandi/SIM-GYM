<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('membership_id')
                ->constrained('memberships')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['membership_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_groups');
    }
};
