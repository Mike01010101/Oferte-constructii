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
            // Coloane pentru redenumirea resurselor
            $table->string('material_column_name')->nullable()->after('pdf_price_display_mode');
            $table->string('labor_column_name')->nullable()->after('material_column_name');
            $table->string('equipment_column_name')->nullable()->after('labor_column_name');

            // Coloane pentru afișarea totalurilor pe resurse
            $table->boolean('show_material_total')->default(false)->after('equipment_column_name');
            $table->boolean('show_labor_total')->default(false)->after('show_material_total');
            $table->boolean('show_equipment_total')->default(false)->after('show_labor_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_settings', function (Blueprint $table) {
            $table->dropColumn([
                'material_column_name',
                'labor_column_name',
                'equipment_column_name',
                'show_material_total',
                'show_labor_total',
                'show_equipment_total',
            ]);
        });
    }
};
