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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('method')->nullable(); // cash, card, bank_transfer, paystack, etc.
            $table->decimal('amount', 10, 2);
            $table->string('reference')->nullable(); // transaction ref from Paystack etc.
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};