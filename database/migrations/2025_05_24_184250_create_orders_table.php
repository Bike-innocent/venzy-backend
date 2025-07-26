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






        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->dateTime('order_date');
            $table->foreignId('discount_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);

            $table->enum('payment_status', [
                'unpaid',
                'pending',
                'paid',
                'refunded',
                'failed'
            ])->default('unpaid');
            $table->string('payment_method')->nullable();

            $table->enum('fulfillment_status', [
                'unfulfilled',
                'fulfilled',
                'returned',
                'cancelled'
            ])->default('unfulfilled');

            $table->enum('delivery_status', [
                'in_transit',
                'delivered',
                'failed'
            ])->nullable();

            $table->text('cancel_reason')->nullable();

            // Shipping address snapshot
            $table->string('shipping_full_name');
            $table->string('shipping_phone');
            $table->string('shipping_dial_code')->nullable();
            $table->string('shipping_address_line_1');
            $table->string('shipping_address_line_2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_state');
            $table->string('shipping_country');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};







//   $table->string('tracking_number')->nullable();
//             $table->string('tracking_url')->nullable();
//             $table->string('payment_method')->default('credit_card'); // Default payment method
//             $table->string('shipping_method')->default('standard'); // Default shipping method
//             $table->string('shipping_cost')->default('0.00'); // Default shipping cost
//             $table->string('coupon_code')->nullable(); // Optional coupon code
//             $table->decimal('tax_amount', 10, 2)->default(0.00); // Default tax amount
//             $table->decimal('subtotal_amount', 10, 2)->default(0.00); // Default subtotal amount












//  $table->string('payment_method')->default('credit_card'); // Default payment method
//             $table->string('payment_status')->default('pending'); // Default payment status
//             $table->string('shipping_method')->default('standard'); // Default shipping method
//             $table->string('shipping_cost')->default('0.00'); // Default shipping cost
//             $table->string('coupon_code')->nullable(); // Optional coupon code
//             $table->decimal('discount_amount', 10, 2)->default(0.00); // Default discount amount
//             $table->decimal('tax_amount', 10, 2)->default(0.00); // Default tax amount
//             $table->decimal('subtotal_amount', 10, 2)->default(0.00); // Default subtotal amount