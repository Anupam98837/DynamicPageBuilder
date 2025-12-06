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
        Schema::table('header_menus', function (Blueprint $table) {
            // Rename existing url -> page_url
            $table->renameColumn('url', 'page_url');

            // Page-level fields (user-entered)
            $table->string('page_slug', 160)
                  ->nullable()
                  ->unique()
                  ->after('slug');

            $table->string('page_shortcode', 100)
                  ->nullable()
                  ->unique()
                  ->after('page_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('header_menus', function (Blueprint $table) {
            // Drop page-level fields
            // (dropping the columns will also drop their unique indexes in MySQL)
            $table->dropColumn(['page_slug', 'page_shortcode']);

            // Rename page_url back to url
            $table->renameColumn('page_url', 'url');
        });
    }
};
