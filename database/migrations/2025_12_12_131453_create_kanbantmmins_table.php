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
        Schema::create('kanbantmmins', function (Blueprint $table) {
            $table->id();
            $table->string('qr_code', 255)->nullable();
            $table->string('manifest_no', 100)->nullable()->index();
            $table->string('keterangan', 255)->nullable();
            $table->datetime('departure_time')->nullable();
            $table->datetime('arrival_time')->nullable();
            $table->string('dock_code', 20)->nullable()->index();
            $table->string('part_address', 100)->nullable();
            $table->string('part_no', 50)->nullable()->index();
            $table->string('order_no', 50)->nullable();
            $table->string('unique_no', 20)->nullable();
            $table->integer('pcs')->default(0);
            $table->string('route', 20)->nullable();
            $table->string('part_name', 255)->nullable();
            $table->string('supplier', 255)->nullable();
            $table->string('supplier_code', 50)->nullable();
            $table->string('customer_address', 100)->nullable();
            $table->datetime('out_time')->nullable();
            $table->string('dock', 100)->nullable();
            $table->string('cycle', 20)->nullable();
            $table->string('address', 100)->nullable();
            $table->integer('plo')->nullable();
            $table->string('conveyance_no', 50)->nullable();
            $table->datetime('last_upload_at')->nullable();
            $table->string('uploaded_by', 100)->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['dock_code', 'manifest_no']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanbantmmins');
    }
};