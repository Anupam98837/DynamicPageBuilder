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
        Schema::create('department_pages', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // Identifiers
            $table->uuid('uuid')->unique();                   // CHAR(36), public-safe ID
            $table->string('slug', 200)->unique();            // URL slug (e.g. "cse-about-msit")
            $table->string('title', 200)->index();            // Page title

            // Shortcode & type
            $table->string('shortcode', 12);                  // e.g. "dept_about", etc.
            $table->string('page_type', 30)                   // e.g. "page", "fragment", "redirect"
                  ->default('page')
                  ->index();

            // Content (for visual editor HTML)
            $table->longText('content_html')->nullable();     // Raw HTML for the page / fragment

            // Includable partials / layout
            $table->string('includable_id', 120)              // Used when this acts as an includable partial
                  ->nullable()
                  ->unique();
            $table->string('layout_key', 100)                 // Optional layout identifier
                  ->nullable()
                  ->index();

            // SEO
            $table->string('meta_description', 255)->nullable();

            // Status + scheduling
            $table->string('status', 20)                      // e.g. "Active", "Inactive", "Archived"
                  ->default('Active')
                  ->index();
            $table->timestamp('published_at')                 // When it was / will be published
                  ->nullable()
                  ->index();

            // Audit (who created / edited)
            $table->unsignedBigInteger('created_by_user_id')
                  ->nullable()
                  ->index();
            $table->unsignedBigInteger('updated_by_user_id')
                  ->nullable()
                  ->index();
            $table->string('created_at_ip', 45)               // IPv4 / IPv6
                  ->nullable();

            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')
                  ->useCurrent()
                  ->useCurrentOnUpdate();

            // Soft delete (Laravel-compatible)
            $table->timestamp('deleted_at')
                  ->nullable()
                  ->index();

            // Foreign keys
            $table->foreign('created_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->foreign('updated_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_pages');
    }
};
