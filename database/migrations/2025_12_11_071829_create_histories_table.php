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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            
            // Basic Info
            $table->string('route');
            $table->string('logistic_partners');
            $table->string('no_dn')->unique();
            $table->string('customers');
            $table->string('dock');
            $table->integer('cycle');
            $table->string('address', 50);
            
            // Timeline - Journey tracking
            $table->date('pulling_date')->nullable();
            $table->time('pulling_time')->nullable();
            $table->date('delivery_date')->nullable();
            $table->time('delivery_time')->nullable();
            $table->datetime('scan_to_shipping')->nullable();
            $table->datetime('arrival')->nullable(); // Waktu truk tiba
            $table->datetime('scan_to_delivery')->nullable();
            $table->datetime('completed_at'); // Waktu selesai/dipindah ke history
            
            // Status & Duration
            $table->string('final_status')->default('completed'); // normal, delay, completed
            $table->float('total_business_hours')->default(0); // Total jam bisnis dari scan_to_delivery sampai completed
            
            // User tracking
            $table->string('moved_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('route');
            $table->index('customers');
            $table->index('final_status');
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};