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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code')->default('NGN');  // ISO code: NGN, USD, GHS
            $table->string('currency_symbol')->default('₦');
            $table->string('currency_position')->default('left'); // left or right
            $table->string('thousand_separator')->default(','); // e.g., ','
            $table->string('decimal_separator')->default('.'); // e.g., '.'
            $table->integer('decimal_places')->default(2); // e.g., 2
            $table->boolean('enable_tax')->default(false); // Enable or disable tax calculations                
            $table->decimal('tax_rate', 5, 2)->default(0.00); // e.g., 7.50 for 7.5%
            $table->boolean('enable_shipping')->default(true); // Enable or disable shipping calculations
            $table->boolean('enable_discount')->default(true); // Enable or disable discounts
            $table->boolean('enable_wishlist')->default(true); // Enable or disable wishlist feature
            $table->boolean('enable_reviews')->default(true); // Enable or disable product reviews
            $table->boolean('enable_guest_checkout')->default(false); // Allow guest checkout
            $table->boolean('enable_order_tracking')->default(true); // Enable order tracking feature
                    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};