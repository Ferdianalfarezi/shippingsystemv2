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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('part_no')->nullable();
            $table->string('customer_code')->nullable();
            $table->string('model')->nullable();
            $table->string('part_name')->nullable();
            $table->string('qty_kbn')->nullable();
            $table->string('line')->nullable();
            $table->string('rack_no')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
