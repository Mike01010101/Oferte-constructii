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
        // Modificăm tabela pentru itemii ofertei
        Schema::table('offer_items', function (Blueprint $table) {
            // Schimbăm precizia coloanelor pentru a permite 4 zecimale
            $table->decimal('material_price', 15, 4)->default(0)->change();
            $table->decimal('labor_price', 15, 4)->default(0)->change();
            $table->decimal('equipment_price', 15, 4)->default(0)->change();
            $table->decimal('total', 15, 4)->change();
        });

        // Modificăm tabela pentru itemii situației de plată
        Schema::table('payment_statement_items', function (Blueprint $table) {
            $table->decimal('material_price', 15, 4)->default(0)->change();
            $table->decimal('labor_price', 15, 4)->default(0)->change();
            $table->decimal('equipment_price', 15, 4)->default(0)->change();
            $table->decimal('total', 15, 4)->change();
        });
    }

    public function down(): void
    {
        // Revertim la starea inițială cu 2 zecimale
        Schema::table('offer_items', function (Blueprint $table) {
            $table->decimal('material_price', 15, 2)->default(0)->change();
            $table->decimal('labor_price', 15, 2)->default(0)->change();
            $table->decimal('equipment_price', 15, 2)->default(0)->change();
            $table->decimal('total', 15, 2)->change();
        });

        Schema::table('payment_statement_items', function (Blueprint $table) {
            $table->decimal('material_price', 15, 2)->default(0)->change();
            $table->decimal('labor_price', 15, 2)->default(0)->change();
            $table->decimal('equipment_price', 15, 2)->default(0)->change();
            $table->decimal('total', 15, 2)->change();
        });
    }
};
