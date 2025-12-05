<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lp_configs', function (Blueprint $table) {
            $table->id();
            $table->string('route')->unique();
            $table->string('logistic_partner');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lp_configs');
    }
};