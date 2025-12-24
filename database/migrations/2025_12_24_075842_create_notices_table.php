<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // External UUID (CHAR(36)) + UNIQUE
            $table->uuid('uuid')->unique();

            // Optional department-specific notice (FK -> departments.id)
            $table->unsignedBigInteger('department_id')->nullable();

            // Core fields
            $table->string('title', 255);
            $table->string('slug', 160)->unique(); // Human-readable unique slug
            $table->longText('body');              // Full content (HTML allowed)

            // Cover image path
            $table->string('cover_image', 255)->nullable();

            // Optional: multiple attachments list
            $table->json('attachments_json')->nullable();

            // Show on homepage featured section (TINYINT(1) DEFAULT 0)
            $table->boolean('is_featured_home')->default(false);

            // draft/published/archived (DEFAULT 'draft')
            $table->string('status', 20)->default('draft');

            // Visibility window
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expire_at')->nullable();

            // Optional analytics counter (DEFAULT 0)
            $table->unsignedBigInteger('views_count')->default(0);

            // Creator user ID (FK -> users.id)
            $table->unsignedBigInteger('created_by')->nullable();

            // Created/Updated time
            $table->timestamps(); // created_at, updated_at (both nullable timestamps)

            // IP audit fields
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete timestamp with INDEX (as per schema)
            $table->timestamp('deleted_at')->nullable()->index();

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
        Schema::dropIfExists('notices');
    }
};
