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
        Schema::create('header_menus', function (Blueprint $table) {
            // Primary keys / identity
            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();          // app-level stable id

            // Hierarchy (self FK)
            $table->unsignedBigInteger('parent_id')->nullable(); // parent menu item

            // Core fields
            $table->string('title', 150);
            $table->text('description')->nullable();

            // Routing / identification
            $table->string('slug', 160)->unique();       // unique slug (auto-generated)
            $table->string('shortcode', 100)->nullable()->unique(); // optional shortcode, unique if present
            $table->string('url', 255)->nullable();      // optional absolute/relative URL

            // Behaviour / ordering
            $table->unsignedInteger('position')->default(0); // order within siblings
            $table->boolean('active')->default(true);        // show/hide in header

            // Timestamps & soft delete
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')
                  ->useCurrent()
                  ->useCurrentOnUpdate();
            $table->softDeletes(); // deleted_at

            // Audit fields (same style as your other tables)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Indexes
            $table->index(['parent_id', 'position']);   // fast tree + ordering
            $table->index('active');

            // Self-referencing FK
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('header_menus')
                  ->onDelete('cascade');               // delete subtree when parent removed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('header_menus');
    }
};
