<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_texts', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->string('speed')->default('normal'); // slow, normal, fast
            $table->string('background_color')->default('#1a1a1a');
            $table->string('text_color')->default('#fbbf24');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_texts');
    }
};