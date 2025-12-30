<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('successful_entrepreneurs', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // PK
            $table->bigIncrements('id');

            // External UUID (CHAR(36), UNIQUE, NOT NULL)
            $table->uuid('uuid')->unique();

            // Optional department-specific (FK -> departments.id, nullable)
            $table->unsignedBigInteger('department_id')->nullable();
            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->nullOnDelete();

            // Optional linked user (FK -> users.id, nullable, INDEX)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            // Human-readable unique slug (VARCHAR(160), UNIQUE, NOT NULL)
            $table->string('slug', 160)->unique();

            // Required fields
            $table->string('name', 120);

            // Optional fields
            $table->string('title', 255)->nullable();
            $table->longText('description')->nullable();           // HTML allowed
            $table->string('photo_url', 255)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('company_logo_url', 255)->nullable();
            $table->string('company_website_url', 255)->nullable();
            $table->string('industry', 120)->nullable();

            // Year founded (YEAR, nullable, INDEX)
            $table->year('founded_year')->nullable()->index();

            // Highlight date (DATE, nullable, INDEX)
            $table->date('achievement_date')->nullable()->index();

            // Highlights (LONGTEXT, nullable, HTML allowed)
            $table->longText('highlights')->nullable();

            // Social links (JSON, nullable)
            $table->json('social_links_json')->nullable();

            // Featured on home (TINYINT(1), default 0, NOT NULL)
            $table->boolean('is_featured_home')->default(false);

            // Display order (INT UNSIGNED, default 0, INDEX, NOT NULL)
            $table->unsignedInteger('sort_order')->default(0)->index();

            // Status (VARCHAR(20), default 'draft', INDEX, NOT NULL)
            $table->string('status', 20)->default('draft')->index();

            // Visibility window (TIMESTAMP nullable, INDEX)
            $table->timestamp('publish_at')->nullable()->index();
            $table->timestamp('expire_at')->nullable()->index();

            // Optional analytics (BIGINT UNSIGNED, default 0, NOT NULL)
            $table->unsignedBigInteger('views_count')->default(0);

            // Creator user ID (FK -> users.id, nullable)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            // Timestamps
            $table->timestamps();

            // IP audit
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete (TIMESTAMP nullable, INDEX)
            $table->softDeletes();
            $table->index('deleted_at');

            // Extra metadata (JSON, nullable)
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('successful_entrepreneurs', function (Blueprint $table) {
            // Drop FKs first
            $table->dropForeign(['department_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('successful_entrepreneurs');
    }
};
