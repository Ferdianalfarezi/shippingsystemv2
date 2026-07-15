<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adm_runouts', function (Blueprint $table) {
            $table->id();
            $table->string('part_no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adm_runouts');
    }
};