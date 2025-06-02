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
        // Schema::create('order_items', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
        //     $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
        //     $table->integer('quantity');
        //     $table->decimal('price', 10, 2); // Store the price at the time of order
        //     $table->timestamps();
        //     $table->softDeletes();
        // });


        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // optional but useful
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade'); // 🆕
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // store price at time of order
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};