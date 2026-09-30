<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addressfji', function (Blueprint $table) {
            // string() = VARCHAR(255)
            $table->string('kategori')->nullable()->after('rack_no');
        });
    }

    public function down(): void
    {
        Schema::table('addressfji', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};