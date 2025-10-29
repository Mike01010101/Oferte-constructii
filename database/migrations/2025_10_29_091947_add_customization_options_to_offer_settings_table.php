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
        $table->decimal('vat_percentage', 5, 2)->default(19.00)->after('suffix');
        
        // Coloane vizibile
        $table->boolean('show_material_column')->default(true)->after('vat_percentage');
        $table->boolean('show_labor_column')->default(true)->after('show_material_column');
        $table->boolean('show_equipment_column')->default(true)->after('show_labor_column');
        $table->boolean('show_unit_price_column')->default(false)->after('show_equipment_column');

        // Recapitulatii
        $table->boolean('show_summary_block')->default(false)->after('show_unit_price_column');
        $table->decimal('summary_cam_percentage', 5, 2)->default(0.00)->after('show_summary_block');
        $table->decimal('summary_indirect_percentage', 5, 2)->default(0.00)->after('summary_cam_percentage');
        $table->decimal('summary_profit_percentage', 5, 2)->default(0.00)->after('summary_indirect_percentage');
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
