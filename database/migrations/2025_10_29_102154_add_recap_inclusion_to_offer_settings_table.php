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
        $table->boolean('include_summary_in_prices')->default(false)->after('show_summary_block');
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
