<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanbanadms', function (Blueprint $table) {
            $table->id();
            $table->string('plant_code')->nullable();
            $table->string('shop_code')->nullable();
            $table->string('part_category')->nullable();
            $table->string('route')->nullable();
            $table->string('lp')->nullable();
            $table->string('trip')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('vendor_alias')->nullable();
            $table->string('vendor_site')->nullable();
            $table->string('vendor_site_alias')->nullable();
            $table->string('order_no')->nullable();
            $table->string('po_number')->nullable();
            $table->string('calc_date')->nullable();
            $table->string('order_date')->nullable();
            $table->string('order_time')->nullable();
            $table->string('del_date')->nullable();
            $table->string('del_time')->nullable();
            $table->string('del_cycle')->nullable();
            $table->string('doc_no')->nullable();
            $table->string('rec_status')->nullable();
            $table->string('dn_type')->nullable();
            $table->string('rec_date')->nullable();
            $table->string('rec_by')->nullable();
            $table->string('part_no')->nullable();
            $table->string('part_name')->nullable();
            $table->string('job_no')->nullable();
            $table->string('lane')->nullable();
            $table->string('qty_kbn')->nullable();
            $table->string('order_kbn')->nullable();
            $table->string('order_pcs')->nullable();
            $table->string('qty_receive')->nullable();
            $table->string('qty_balance')->nullable();
            $table->string('cancel_status')->nullable();
            $table->string('remark')->nullable();
            $table->string('uploaded_by')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanbanadms');
    }
};