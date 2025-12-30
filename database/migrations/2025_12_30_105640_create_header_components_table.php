<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('header_components', function (Blueprint $table) {

            // Primary Key
            $table->bigIncrements('id');

            // External UUID
            $table->char('uuid', 36)->unique();

            // Human-readable unique slug
            $table->string('slug', 160)->unique();

            // Logo image URL/path
            $table->string('primary_logo_url', 255);
            $table->string('secondary_logo_url', 255);

            // Header text (e.g., “HALLIENZ”)
            $table->string('header_text', 255);

            // Multiple sentences in one array
            $table->json('rotating_text_json');

            // Image or GIF URL/path
            $table->string('admission_badge_url', 255);

            // Link to Admission Page (nullable)
            $table->string('admission_link_url', 255)->nullable();

            // Multiple images list (recruiters.id[])
            $table->json('partner_logos_json');

            // Multiple images list
            $table->json('affiliation_logos_json');

            // Creator user ID (nullable)
            $table->unsignedBigInteger('created_by')->nullable();

            // Timestamps (nullable as per schema)
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // IP tracking (nullable)
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete timestamp + INDEX
            $table->softDeletes();
            $table->index('deleted_at');

            // Extra metadata (nullable)
            $table->json('metadata')->nullable();

            // FK -> users.id (nullable + set null on delete)
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('header_components', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('header_components');
    }
};
