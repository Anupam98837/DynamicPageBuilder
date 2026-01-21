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
        // ✅ header_menus.page_slug : UNIQUE -> normal
        if (Schema::hasTable('header_menus') && Schema::hasColumn('header_menus', 'page_slug')) {
            Schema::table('header_menus', function (Blueprint $table) {
                $table->dropUnique(['page_slug']); // header_menus_page_slug_unique
            });
        }

        // ✅ pages_submenu.page_slug : UNIQUE -> normal
        if (Schema::hasTable('pages_submenu') && Schema::hasColumn('pages_submenu', 'page_slug')) {
            Schema::table('pages_submenu', function (Blueprint $table) {
                $table->dropUnique(['page_slug']); // pages_submenu_page_slug_unique
            });
        }

        // ✅ top_header_menus.page_slug : UNIQUE -> normal
        if (Schema::hasTable('top_header_menus') && Schema::hasColumn('top_header_menus', 'page_slug')) {
            Schema::table('top_header_menus', function (Blueprint $table) {
                $table->dropUnique(['page_slug']); // top_header_menus_page_slug_unique
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ⚠️ Rollback will make it UNIQUE again (will fail if duplicates exist)

        if (Schema::hasTable('header_menus') && Schema::hasColumn('header_menus', 'page_slug')) {
            Schema::table('header_menus', function (Blueprint $table) {
                $table->unique('page_slug');
            });
        }

        if (Schema::hasTable('pages_submenu') && Schema::hasColumn('pages_submenu', 'page_slug')) {
            Schema::table('pages_submenu', function (Blueprint $table) {
                $table->unique('page_slug');
            });
        }

        if (Schema::hasTable('top_header_menus') && Schema::hasColumn('top_header_menus', 'page_slug')) {
            Schema::table('top_header_menus', function (Blueprint $table) {
                $table->unique('page_slug');
            });
        }
    }
};
