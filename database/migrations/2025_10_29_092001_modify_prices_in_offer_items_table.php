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
        Schema::table('offer_items', function (Blueprint $table) {
            $table->decimal('material_price', 15, 2)->default(0.00)->after('unit_measure');
            $table->decimal('labor_price', 15, 2)->default(0.00)->after('material_price');
            $table->decimal('equipment_price', 15, 2)->default(0.00)->after('labor_price');

            // Facem vechea coloană `unit_price` opțională sau o ștergem
            $table->dropColumn('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_items', function (Blueprint $table) {
            //
        });
    }
};
