<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliphpms', function (Blueprint $table) {
            $table->string('rcv_type', 1)->nullable()->after('inv_cat');
        });
    }

    public function down(): void
    {
        Schema::table('sliphpms', function (Blueprint $table) {
            $table->dropColumn('rcv_type');
        });
    }
};