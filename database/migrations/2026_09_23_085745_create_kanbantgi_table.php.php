<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanbantgi', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('dn_no')->nullable();
            $table->string('po_no')->nullable();
            $table->unsignedInteger('total_parts')->default(0);
            $table->unsignedInteger('matched')->default(0);
            $table->unsignedInteger('unmatched')->default(0);
            $table->json('items_meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanbantgi');
    }
};