<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom timeline dari Preparation agar terbawa ke Delivery dan History
     */
    public function up(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            // Timeline dari Preparation - tambahkan jika belum ada
            $table->date('pulling_date')->nullable()->after('address');
            $table->time('pulling_time')->nullable()->after('pulling_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            $table->dropColumn(['pulling_date', 'pulling_time']);
        });
    }
};