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
        // Schema::table('settings', function (Blueprint $table) {
        //     // Currency
        //     $table->string('currency_code')->default('NGN')->change();     // Already exists
        //     $table->string('currency_symbol')->default('₦')->change();     // Already exists

        //     // Tax
        //     $table->decimal('tax_rate', 5, 2)->default(0); // e.g. 7.50 (%)

        //     // Shipping
        //     $table->enum('shipping_type', ['flat', 'dynamic'])->default('flat');
        //     $table->decimal('flat_rate', 10, 2)->nullable(); // If flat

        //     // Contact Info (for footer / pages)
        //     $table->string('store_email')->nullable();
        //     $table->string('store_phone_1')->nullable();
        //     $table->string('store_phone_2')->nullable();
        //     $table->text('store_address')->nullable();

        //     // Store Info
        //     $table->string('store_name')->nullable();
        //     $table->string('logo_path')->nullable();

        //     // Maintenance Mode
        //     $table->boolean('maintenance_mode')->default(false);

        //     // Optional: SEO default values
        //     $table->string('default_meta_title')->nullable();
        //     $table->text('default_meta_description')->nullable();

        //     // Optional: Social Media Links
        //     $table->string('facebook_link')->nullable();
        //     $table->string('x_link')->nullable();
        //     $table->string('instagram_link')->nullable();
        //     $table->string('tiktok_link')->nullable();
        //     $table->string('youtube_link')->nullable();

        // });



        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_group_id')->constrained()->onDelete('cascade');

            $table->string('key');       // e.g. 'store_name', 'currency_code'
            $table->text('value')->nullable();

            $table->timestamps();

            $table->unique(['setting_group_id', 'key']); // Unique per group
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



















//  $table->string('currency_position')->default('left'); // left or right
//             $table->string('thousand_separator')->default(','); // e.g., ','
//             $table->string('decimal_separator')->default('.'); // e.g., '.'
//             $table->integer('decimal_places')->default(2); // e.g., 2
//             $table->boolean('enable_tax')->default(false); // Enable or disable tax calculations
//             $table->decimal('tax_rate', 5, 2)->default(0.00); // e.g., 7.50 for 7.5%
//             $table->boolean('enable_shipping')->default(true); // Enable or disable shipping calculations
//             $table->boolean('enable_discount')->default(true); // Enable or disable discounts
//             $table->boolean('enable_wishlist')->default(true); // Enable or disable wishlist feature
//             $table->boolean('enable_reviews')->default(true); // Enable or disable product reviews
//             $table->boolean('enable_guest_checkout')->default(false); // Allow guest checkout
//             $table->boolean('enable_order_tracking')->default(true); // Enable order tracking feature