<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->string('route');
            $table->string('logistic_partners');
            $table->string('no_dn')->unique();
            $table->string('customers');
            $table->string('dock');
            $table->date('delivery_date');
            $table->time('delivery_time');
            $table->datetime('arrival')->nullable(); // Kosong sampai di-scan
            $table->integer('cycle');
            $table->string('address'); // Shipping 1, Shipping 2, ..., Shipping Ex 1, dll
            $table->enum('status', ['advance', 'normal', 'delay', 'on_loading'])->default('normal');
            $table->datetime('scan_to_shipping'); // Waktu data dipindah dari preparations
            $table->timestamps();
            $table->softDeletes();
            
            // Index untuk performa query
            $table->index('route');
            $table->index('status');
            $table->index('delivery_date');
            $table->index(['delivery_date', 'delivery_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};