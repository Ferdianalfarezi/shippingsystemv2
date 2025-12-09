<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shippings', function (Blueprint $table) {
            $table->string('moved_by')->nullable()->after('scan_to_shipping');
        });
    }

    public function down()
    {
        Schema::table('shippings', function (Blueprint $table) {
            $table->dropColumn('moved_by');
        });
    }
};