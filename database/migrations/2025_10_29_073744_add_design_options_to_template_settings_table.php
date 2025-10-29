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
    Schema::table('template_settings', function (Blueprint $table) {
        $table->string('layout')->default('classic')->after('company_id');
        $table->string('font_family')->default('Roboto')->after('layout');
        $table->string('table_style')->default('grid')->after('font_family');
        $table->string('document_title')->default('OFERTĂ COMERCIALĂ')->after('accent_color');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_settings', function (Blueprint $table) {
            //
        });
    }
};
