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
    Schema::table('kanbanhpms', function (Blueprint $table) {
        $table->timestamp('expires_at')->nullable()->after('uploaded_by');
        $table->index('expires_at'); // biar query delete cepet
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanbanhpms', function (Blueprint $table) {
            //
        });
    }
};
