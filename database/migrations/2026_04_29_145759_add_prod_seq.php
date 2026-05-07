<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliphpms', function (Blueprint $table) {
            $table->string('prod_seq', 12)->nullable()->after('ship');
        });
    }

    public function down(): void
    {
        Schema::table('sliphpms', function (Blueprint $table) {
            $table->dropColumn('prod_seq');
        });
    }
};