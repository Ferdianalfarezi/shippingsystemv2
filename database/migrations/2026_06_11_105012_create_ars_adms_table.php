<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ars_adms', function (Blueprint $table) {
            $table->id();
            $table->string('part_no')->unique();
            $table->string('min')->nullable();
            $table->string('max')->nullable();
            $table->string('part_cat')->nullable();
            $table->string('packing_type')->nullable();
            $table->string('area_code')->nullable();
            $table->string('part_type')->nullable();
            $table->string('wh_zone')->nullable();
            $table->string('rack_no')->nullable();
            $table->string('rack_layer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ars_adms');
    }
};