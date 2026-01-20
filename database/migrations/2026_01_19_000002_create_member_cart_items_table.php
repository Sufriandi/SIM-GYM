<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('produk_id');

            $table->unsignedInteger('quantity')->default(1);

            // snapshot untuk UI (agar tidak berubah saat harga/nama berubah)
            $table->decimal('price', 14, 2)->default(0);
            $table->string('name')->nullable();
            $table->string('photo')->nullable();
            $table->string('category')->nullable();

            $table->timestamps();

            $table->unique(['cart_id', 'produk_id']);

            $table->foreign('cart_id')
                ->references('id')->on('member_carts')
                ->onDelete('cascade');

            $table->foreign('produk_id')
                ->references('id')->on('produks')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_cart_items');
    }
};
