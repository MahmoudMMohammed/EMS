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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->string('name');
            $table->string('governorate');
            $table->string('address');
            $table->foreignId('host_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('capacity');
            $table->string('open_time');
            $table->string('close_time');
            $table->double('reservation_price');
            $table->double('x_position');
            $table->double('y_position');
            $table->string('logo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
