<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanbanbanktgi', function (Blueprint $table) {
            $table->id();
            $table->string('part_no')->unique();
            $table->string('original_filename');
            $table->string('file_path');
            $table->unsignedInteger('labels_per_page')->default(3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanbanbanktgi');
    }
};