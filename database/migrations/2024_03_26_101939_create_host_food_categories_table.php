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
        Schema::create('host_food_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_category_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('host_food_categories');
    }
};
