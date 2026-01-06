<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_notices', function (Blueprint $table) {
            // Primary key
            $table->id(); // BIGINT UNSIGNED, AUTO_INCREMENT, PK

            // External UUID
            $table->char('uuid', 36)->unique();

            // Main fields
            $table->string('title', 255);
            $table->string('slug', 160)->unique();
            $table->longText('body');

            // Optional fields
            $table->string('cover_image', 255)->nullable();
            $table->json('attachments_json')->nullable();

            // Flags / status
            $table->tinyInteger('is_featured_home')->default(0); // TINYINT(1) DEFAULT 0
            $table->string('status', 20)->default('draft');      // draft/published/archived

            // Publish / expiry
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expire_at')->nullable();

            // Analytics
            $table->unsignedBigInteger('views_count')->default(0);

            // Creator
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();

            // Timestamps (nullable by default in Laravel)
            $table->timestamps(); // created_at, updated_at (nullable)

            // IP audit
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete + index (schema says deleted_at has INDEX)
            $table->softDeletes();       // deleted_at (nullable)
            $table->index('deleted_at'); // ensure index exists as per schema

            // Extra metadata
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_notices');
    }
};
