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
        //    Schema::create('addresses', function (Blueprint $table) {
        //         $table->id();
        //         $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign key to users
        //         $table->string('address_line_1');
        //         $table->string('address_line_2')->nullable();
        //         $table->string('city');
        //         $table->string('state');
        //         $table->string('postal_code');
        //         $table->string('country');
        //         $table->timestamps();
        //         $table->softDeletes();
        //     });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('full_name');
            $table->string('phone');
             $table->string('dial_code')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable(); // optional secondary line
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->boolean('is_default')->default(false); // one default per user
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};












  // $table->string('postal_code')->nullable();