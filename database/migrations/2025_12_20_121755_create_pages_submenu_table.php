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
        Schema::create('pages_submenu', function (Blueprint $table) {

            // Primary keys / identity
            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();          // app-level stable id

            // ✅ Link to pages
            $table->unsignedBigInteger('page_id')->index();

            // Hierarchy (self FK)
            $table->unsignedBigInteger('parent_id')->nullable(); // parent submenu item

            // Core fields
            $table->string('title', 150);
            $table->text('description')->nullable();

            // Routing / identification (same as header_menus final state)
            $table->string('slug', 160)->unique();               // unique slug (auto-generated)
            $table->string('shortcode', 100)->nullable()->unique(); // optional shortcode, unique if present
            $table->string('page_url', 255)->nullable();          // renamed from url -> page_url

            // Page-level fields (user-entered)
            $table->string('page_slug', 160)->nullable()->unique();
            $table->string('page_shortcode', 100)->nullable()->unique();

            // Behaviour / ordering
            $table->unsignedInteger('position')->default(0); // order within siblings
            $table->boolean('active')->default(true);        // show/hide

            // Timestamps & soft delete
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes(); // deleted_at

            // Audit fields (same style as your other tables)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Indexes (same idea as header_menus + page relation)
            $table->index(['parent_id', 'position']);
            $table->index('active');

            // ✅ FK: page_id -> pages.id
            $table->foreign('page_id')
                  ->references('id')
                  ->on('pages')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // ✅ Self-referencing FK: parent_id -> pages_submenu.id
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('pages_submenu')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages_submenu', function (Blueprint $table) {
            $table->dropForeign(['page_id']);
            $table->dropForeign(['parent_id']);
        });

        Schema::dropIfExists('pages_submenu');
    }
};
