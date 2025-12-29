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
        Schema::table('pages_submenu', function (Blueprint $table) {
            // Add after page_shortcode to keep it near page-related fields (optional)
            $table->string('includable_path', 255)->nullable()->after('page_shortcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages_submenu', function (Blueprint $table) {
            $table->dropColumn('includable_path');
        });
    }
};
