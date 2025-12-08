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
        Schema::create('adm_lead_times', function (Blueprint $table) {
            $table->id();
            $table->string('route', 50);
            $table->string('dock', 50);
            $table->string('cycle', 10);
            $table->time('lead_time')->default('03:00:00'); // Default 3 jam
            $table->timestamps();

            // Unique constraint: satu kombinasi route + dock + cycle = satu lead time
            $table->unique(['route', 'dock', 'cycle'], 'adm_lead_time_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_lead_times');
    }
};