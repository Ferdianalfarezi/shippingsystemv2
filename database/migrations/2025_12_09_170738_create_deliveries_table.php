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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('route');
            $table->string('logistic_partners');
            $table->string('no_dn')->unique();
            $table->string('customers');
            $table->string('dock');
            $table->integer('cycle')->default(1);
            $table->string('address', 50);
            $table->string('status')->default('pending'); // pending, on_delivery, delivered
            $table->timestamp('scan_to_delivery')->nullable(); // waktu masuk ke tabel delivery
            $table->string('moved_by')->nullable(); // siapa yang memindahkan
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('route');
            $table->index('no_dn');
            $table->index('status');
            $table->index('scan_to_delivery');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};