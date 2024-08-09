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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('phone_number')->unique()->nullable();
            $table->double('balance')->default(0.0);
            $table->date('birth_date')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('preferred_language')->default('en');
            $table->string('preferred_currency')->default('SYP');
            $table->string('about_me')->nullable();
            $table->string('place_of_residence')->nullable();
            $table->string('gender')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
