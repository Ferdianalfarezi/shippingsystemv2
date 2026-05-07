<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliphpms', function (Blueprint $table) {
            $table->id();
            $table->string('di_no', 20);
            $table->string('part_no', 30)->nullable();
            $table->string('part_name', 100)->nullable();
            $table->string('part_color', 20)->nullable();
            $table->string('from', 20)->nullable();
            $table->string('to', 20)->nullable();
            $table->string('ps_code', 20)->nullable();
            $table->string('inv_cat', 5)->nullable();
            $table->string('kd_lot_no', 20)->nullable();
            $table->string('supply_address', 10)->nullable();
            $table->string('ms_id', 5)->nullable();
            $table->string('ship', 10)->nullable();
            $table->string('seq_no', 30)->nullable();
            $table->string('datetime', 20)->nullable();
            $table->string('qty', 10)->nullable();
            $table->string('uploaded_by')->nullable();
            $table->timestamp('last_upload_at')->nullable();
            $table->timestamp('expires_at')->nullable();   // +7 hari dari last_upload_at
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sliphpms');
    }
};