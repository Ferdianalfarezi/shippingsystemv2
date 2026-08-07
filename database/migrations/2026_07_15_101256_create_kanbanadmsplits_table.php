<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanbanadmsplits', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();     // dipake di URL: labels-meta & download
            $table->string('original_filename');
            $table->string('file_path');                // path relatif di storage/app, misal kanban/history/xxx.pdf
            $table->unsignedInteger('total_labels')->default(0);
            $table->unsignedInteger('matched')->default(0);
            $table->unsignedInteger('unmatched')->default(0);
            $table->unsignedInteger('unmatched_no_text')->default(0);
            $table->unsignedInteger('unmatched_no_extract')->default(0);
            $table->unsignedInteger('unmatched_no_master')->default(0);
            $table->unsignedInteger('unmatched_no_rack')->default(0);
            $table->json('labels_meta')->nullable();     // dipake buat filter SHOP/Plant
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanbanadmsplits');
    }
};