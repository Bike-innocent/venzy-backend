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
    
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            // Reference to the user (nullable for guest carts)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            // Optionally track guest sessions
            $table->string('session_id')->nullable()->index();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('quantity')->default(1);
            // Price at time of adding to cart (can be different from current product price)
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 10, 2)->nullable(); // if you want to store discounts per item
            $table->boolean('is_checked_out')->default(false); // useful to distinguish active carts vs archived ones

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
