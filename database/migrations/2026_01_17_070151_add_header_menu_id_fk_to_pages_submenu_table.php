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

            /**
             * ✅ Make page_id nullable (not required)
             * Note: column modification may require "doctrine/dbal" in some Laravel setups.
             */
            $table->dropForeign(['page_id']);
            $table->unsignedBigInteger('page_id')->nullable()->change();

            // ✅ Re-add FK: pages_submenu.page_id -> pages.id
            $table->foreign('page_id')
                  ->references('id')
                  ->on('pages')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // ✅ Add ONLY ONE column (nullable FK)
            $table->unsignedBigInteger('header_menu_id')
                  ->nullable()
                  ->after('page_id')
                  ->index();

            // ✅ FK: pages_submenu.header_menu_id -> header_menus.id
            $table->foreign('header_menu_id')
                  ->references('id')
                  ->on('header_menus')
                  ->onDelete('set null')   // if header menu deleted, keep submenu but unlink
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages_submenu', function (Blueprint $table) {

            // ✅ Drop FK first (header_menu_id)
            $table->dropForeign(['header_menu_id']);

            // ✅ Drop index
            $table->dropIndex(['header_menu_id']);

            // ✅ Drop column
            $table->dropColumn('header_menu_id');

            /**
             * ✅ Revert page_id back to NOT NULL (required)
             */
            $table->dropForeign(['page_id']);
            $table->unsignedBigInteger('page_id')->nullable(false)->change();

            // ✅ Re-add FK: pages_submenu.page_id -> pages.id
            $table->foreign('page_id')
                  ->references('id')
                  ->on('pages')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }
};
