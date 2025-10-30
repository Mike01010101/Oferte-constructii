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
            $table->string('stamp_path')->nullable()->after('footer_text');
            $table->integer('stamp_size')->default(150)->after('stamp_path');
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
