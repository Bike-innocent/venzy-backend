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
        Schema::create('fulfillments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            $table->string('carrier_name')->nullable(); // e.g. GIG, DHL, FedEx
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->text('notes')->nullable(); // extra info like drop-off location or special instructions

            $table->timestamp('dispatched_at')->nullable(); // when the package was sent
            $table->timestamp('delivered_at')->nullable();  // when delivery was confirmed

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fulfillments');
    }
};