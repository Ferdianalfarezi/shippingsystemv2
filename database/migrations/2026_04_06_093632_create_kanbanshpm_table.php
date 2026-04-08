<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanbanhpms', function (Blueprint $table) {
            $table->id();
            $table->string('di_no', 20)->nullable();          // DI261610013582
            $table->string('item_seq', 10)->nullable();        // 00001
            $table->string('part_no', 20)->nullable();         // 11941-5R0-0001
            $table->string('part_name', 100)->nullable();      // STAY COMP, CONVERTER
            $table->string('seq_no', 20)->nullable();          // 202604130020
            $table->string('kd_lot_no', 30)->nullable();       // HPM 00202603005301
            $table->string('supply_address', 20)->nullable();  // EG001
            $table->string('from', 20)->nullable();            // 1S017-00
            $table->string('to', 30)->nullable();              // AE02-2SAF01
            $table->string('inventory_category', 20)->nullable(); // MEAE02
            $table->string('ps_code', 20)->nullable();         // HPM 024AE
            $table->string('order_class', 5)->nullable();      // 1
            $table->string('datetime', 20)->nullable();        // 09-04 08:00
            $table->string('ship', 50)->nullable();            // kosong dulu
            $table->text('barcode')->nullable();               // generate dari part_no
            $table->string('last_upload_at')->nullable();
            $table->string('uploaded_by', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanbanhpms');
    }
};