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
        Schema::create('milkruns', function (Blueprint $table) {
            $table->id();
            $table->string('customers', 255);
            $table->string('route', 255);
            $table->string('logistic_partners', 255)->nullable();
            $table->integer('cycle');
            $table->string('dock', 255)->nullable();
            $table->date('delivery_date');
            $table->time('delivery_time');
            $table->datetime('arrival')->nullable();
            $table->datetime('departure')->nullable();
            $table->enum('status', ['pending', 'advance', 'on_time', 'delay'])->default('pending');
            $table->integer('dn_count')->default(0); // Jumlah DN yang terkait
            $table->json('no_dns')->nullable(); // Array of no_dn
            $table->string('address', 50)->nullable();
            $table->string('moved_by', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index untuk query yang sering
            $table->index(['route', 'cycle']);
            $table->index('status');
            $table->index('delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milkruns');
    }
};