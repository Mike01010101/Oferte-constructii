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
        // Actualizăm tabela template_settings
        Schema::table('template_settings', function (Blueprint $table) {
            // Verificăm dacă coloana 'document_title' NU există înainte de a o adăuga
            if (!Schema::hasColumn('template_settings', 'document_title')) {
                $table->string('document_title')->after('layout')->default('DEVIZ OFERTĂ');
            }
        });

        // Actualizăm tabela offer_settings
        Schema::table('offer_settings', function (Blueprint $table) {
            // Adăugăm noile coloane doar dacă NU există
            if (!Schema::hasColumn('offer_settings', 'numbering_mode')) {
                $table->string('numbering_mode')->default('auto')->after('prefix'); // L-am pus după prefix pentru logică
            }
            if (!Schema::hasColumn('offer_settings', 'show_unit_price_column')) {
                $table->boolean('show_unit_price_column')->default(false)->after('vat_percentage');
            }

            // Ștergem coloana 'suffix' doar dacă există
            if (Schema::hasColumn('offer_settings', 'suffix')) {
                $table->dropColumn('suffix');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Aici definim cum să anulăm modificările, pentru siguranță
        Schema::table('template_settings', function (Blueprint $table) {
            if (Schema::hasColumn('template_settings', 'document_title')) {
                $table->dropColumn('document_title');
            }
        });

        Schema::table('offer_settings', function (Blueprint $table) {
            if (Schema::hasColumn('offer_settings', 'numbering_mode')) {
                $table->dropColumn('numbering_mode');
            }
            if (Schema::hasColumn('offer_settings', 'show_unit_price_column')) {
                $table->dropColumn('show_unit_price_column');
            }
            if (!Schema::hasColumn('offer_settings', 'suffix')) {
                $table->string('suffix')->nullable();
            }
        });
    }
};