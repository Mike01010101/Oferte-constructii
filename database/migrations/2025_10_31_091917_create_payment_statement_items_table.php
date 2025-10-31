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
        Schema::create('payment_statement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_statement_id')->constrained()->onDelete('cascade');
            
            $table->text('description');
            $table->decimal('quantity', 15, 2);
            $table->string('unit_measure');
            $table->decimal('material_price', 15, 2)->default(0);
            $table->decimal('labor_price', 15, 2)->default(0);
            $table->decimal('equipment_price', 15, 2)->default(0);
            $table->decimal('total', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_statement_items');
    }
};
