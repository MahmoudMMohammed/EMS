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
        Schema::create('m_e_h_a_c_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accessory_category_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('main_event_host_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_e_h_a_c_s');
    }
};
