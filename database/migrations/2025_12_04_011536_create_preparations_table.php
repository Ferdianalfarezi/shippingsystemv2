<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preparations', function (Blueprint $table) {
            $table->id();
            $table->string('route');
            $table->string('logistic_partners');
            $table->string('no_dn')->unique();
            $table->string('customers');
            $table->string('dock');
            $table->date('delivery_date');
            $table->time('delivery_time');
            $table->integer('cycle');
            $table->date('pulling_date');
            $table->time('pulling_time');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preparations');
    }
};