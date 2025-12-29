<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('success_stories', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // External UUID (unique)
            $table->uuid('uuid')->unique();

            // Optional department-specific story
            $table->unsignedBigInteger('department_id')->nullable();

            // Human-readable unique slug
            $table->string('slug', 160)->unique();

            // Student/person name
            $table->string('name', 120);

            // Story headline
            $table->string('title', 255)->nullable();

            // Long description (HTML allowed)
            $table->longText('description')->nullable();

            // Profile photo URL/path
            $table->string('photo_url', 255)->nullable();

            // Exact placement/achievement date
            $table->date('date')->nullable();

            // Year for quick filtering
            $table->year('year')->nullable()->index();

            // Short testimonial quote
            $table->string('quote', 500)->nullable();

            // Optional links (LinkedIn/GitHub/etc.)
            $table->json('social_links_json')->nullable();

            // Show on homepage featured
            $table->boolean('is_featured_home')->default(false);

            // Display order
            $table->unsignedInteger('sort_order')->default(0)->index();

            // draft/published/archived
            $table->string('status', 20)->default('draft')->index();

            // When visible
            $table->timestamp('publish_at')->nullable()->index();

            // Optional expiry
            $table->timestamp('expire_at')->nullable()->index();

            // Optional analytics
            $table->unsignedBigInteger('views_count')->default(0);

            // Creator user ID
            $table->unsignedBigInteger('created_by')->nullable();

            // Created/Updated time
            $table->timestamps(); // created_at & updated_at (nullable by default)

            // Creation/Update IP
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete timestamp
            $table->softDeletes();
            $table->index('deleted_at');

            // Extra metadata
            $table->json('metadata')->nullable();

            // Foreign keys
            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('success_stories', function (Blueprint $table) {
            // Drop FKs first for clean rollback
            $table->dropForeign(['department_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('success_stories');
    }
};
