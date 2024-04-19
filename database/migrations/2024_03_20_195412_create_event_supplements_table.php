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
        Schema::create('event_supplements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_event_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->json('food_details');
            $table->json('drinks_details');
            $table->json('accessories_details');
            $table->double('total_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_supplements');
    }
};
