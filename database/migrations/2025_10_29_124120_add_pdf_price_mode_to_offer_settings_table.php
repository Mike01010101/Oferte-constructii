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
    Schema::table('offer_settings', function (Blueprint $table) {
        $table->string('pdf_price_display_mode')->default('unit')->after('show_unit_price_column'); // 'unit' sau 'total'
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_settings', function (Blueprint $table) {
            //
        });
    }
};
