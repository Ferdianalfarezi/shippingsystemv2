<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pulling_matrices', function (Blueprint $table) {
            $table->id();
            $table->string('route', 100);
            $table->string('dock', 100);
            $table->string('cycle', 20);
            $table->time('pulling_time'); // jam absolut, e.g. 07:30:00
            $table->timestamps();

            // Unique key: satu kombinasi route+dock+cycle hanya boleh ada satu pulling time
            $table->unique(['route', 'dock', 'cycle'], 'pulling_matrices_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pulling_matrices');
    }
};