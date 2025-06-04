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
        // Schema::create('products', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('slug', 10)->unique();
        //     $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
        //     $table->foreignId('colour_id')->constrained()->onDelete('cascade');
        //     $table->foreignId('size_id')->constrained()->onDelete('cascade');
        //     $table->text('description');
        //     $table->decimal('price', 10, 2);
        //     $table->integer('stock_quantity');
        //     $table->timestamps();
        //     $table->softDeletes();
        // });


        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained('brands');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('average_price', 10, 2)->nullable();
            $table->decimal('compared_at_price', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
