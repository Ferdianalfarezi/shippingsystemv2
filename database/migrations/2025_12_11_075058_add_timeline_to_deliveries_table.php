<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom timeline agar data journey terbawa sampai ke History
     */
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Timeline dari Preparation
            $table->date('pulling_date')->nullable()->after('address');
            $table->time('pulling_time')->nullable()->after('pulling_date');
            $table->date('delivery_date')->nullable()->after('pulling_time');
            $table->time('delivery_time')->nullable()->after('delivery_date');
            
            // Timeline dari Shipping
            $table->datetime('scan_to_shipping')->nullable()->after('delivery_time');
            $table->datetime('arrival')->nullable()->after('scan_to_shipping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn([
                'pulling_date',
                'pulling_time', 
                'delivery_date',
                'delivery_time',
                'scan_to_shipping',
                'arrival'
            ]);
        });
    }
};