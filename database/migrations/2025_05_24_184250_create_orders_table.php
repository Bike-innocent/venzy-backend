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
            $table->foreignId('address_id')->constrained()->onDelete('cascade');

            $table->dateTime('order_date');
            $table->foreignId('discount_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);

            $table->decimal('total_amount', 10, 2);

            $table->enum('status', ['processing', 'shipped', 'delivered', 'returns', 'cancelled']);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

















//  $table->string('payment_method')->default('credit_card'); // Default payment method
//             $table->string('payment_status')->default('pending'); // Default payment status
//             $table->string('shipping_method')->default('standard'); // Default shipping method
//             $table->string('shipping_cost')->default('0.00'); // Default shipping cost
//             $table->string('coupon_code')->nullable(); // Optional coupon code
//             $table->decimal('discount_amount', 10, 2)->default(0.00); // Default discount amount
//             $table->decimal('tax_amount', 10, 2)->default(0.00); // Default tax amount
//             $table->decimal('subtotal_amount', 10, 2)->default(0.00); // Default subtotal amount