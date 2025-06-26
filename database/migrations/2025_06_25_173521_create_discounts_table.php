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
        // Schema::create('discounts', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('code')->unique(); // Required to apply manually
        //     $table->enum('type', ['fixed', 'percentage', 'free_shipping']);
        //     $table->decimal('amount', 10, 2)->nullable(); // Only for fixed or percentage
        //     $table->decimal('min_order_amount', 10, 2)->nullable(); // e.g. ₦500+

        //     $table->unsignedInteger('usage_limit')->nullable(); // e.g. 1000 uses
        //     $table->unsignedInteger('used_count')->default(0); // Track usage

        //     $table->boolean('is_active')->default(true);
        //     $table->timestamp('starts_at')->nullable();
        //     $table->timestamp('ends_at')->nullable();

        //     $table->timestamps();
        // });

        Schema::create('discounts', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique()->nullable(); // nullable for auto-applied discounts
            $table->enum('discount_type', ['order', 'product', 'shipping']); // What the discount targets
            $table->enum('value_type', ['fixed', 'percentage']);
            $table->decimal('value', 10, 2)->nullable(); // Discount amount or percentage (based on value_type)

            $table->enum('requirement_type', ['none', 'min_order_amount', 'min_quantity'])->default('none');
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->unsignedInteger('min_quantity')->nullable();

            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};















//  $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade'); // Optional, for product-specific discounts
//             $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade'); // Optional, for category-specific discounts
//             $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Optional, for user-specific discounts
//             $table->text('description')->nullable(); // Optional description of the discount
//             $table->string('minimum_order_amount')->nullable(); // Minimum order amount to apply the        
//             $table->string('maximum_discount_amount')->nullable(); // Maximum discount amount allowed
//             $table->string('applicable_products')->nullable(); // Comma-separated list of product IDs for specific discounts
            
            
//             $table->string('applicable_categories')->nullable(); // Comma-separated list of category IDs for specific discounts
//             $table->string('applicable_users')->nullable(); // Comma-separated list of user IDs for specific discounts
//             $table->string('applicable_brands')->nullable(); // Comma-separated list of brand IDs for specific discounts


//             $table->string('applicable_colours')->nullable(); // Comma-separated list of colour IDs for specific discounts
//             $table->string('applicable_sizes')->nullable(); // Comma-separated list of size IDs for specific discounts
//             $table->string('applicable_product_variants')->nullable(); // Comma-separated list of product variant IDs for specific discounts
//             $table->string('applicable_shipping_methods')->nullable(); // Comma-separated list of shipping method IDs for specific discounts        