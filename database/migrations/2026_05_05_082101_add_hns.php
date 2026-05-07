<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliphpms', function (Blueprint $table) {
            $table->string('loc', 1)->nullable()->after('kd_lot_no');
            $table->string('hns', 1)->nullable()->after('ms_id');
        });
    }

    public function down(): void
    {
        Schema::table('sliphpms', function (Blueprint $table) {
            $table->dropColumn(['loc', 'hns']);
        });
    }
};
