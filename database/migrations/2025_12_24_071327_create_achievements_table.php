<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // External UUID (CHAR(36)) + UNIQUE
            $table->uuid('uuid')->unique();

            // Optional department-specific achievement (FK -> departments.id)
            $table->unsignedBigInteger('department_id')->nullable();

            // Core fields
            $table->string('title', 255);
            $table->string('slug', 160)->unique();         // Human-readable unique slug
            $table->longText('body');                      // Full content (HTML allowed)

            // Media / attachments
            $table->string('cover_image', 255)->nullable(); // Cover image path
            $table->json('attachments_json')->nullable();   // Optional: multiple attachments list

            // Flags / status
            $table->boolean('is_featured_home')->default(false); // TINYINT(1) DEFAULT 0
            $table->string('status', 20)->default('draft');      // draft/published/archived

            // Visibility windows
            $table->timestamp('publish_at')->nullable(); // When it becomes visible
            $table->timestamp('expire_at')->nullable();  // Optional expiry time

            // Analytics counter
            $table->unsignedBigInteger('views_count')->default(0);

            // Creator user ID (FK -> users.id)
            $table->unsignedBigInteger('created_by')->nullable();

            // Timestamps (created_at, updated_at) (nullable by default in Laravel)
            $table->timestamps();

            // Audit IPs
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete timestamp + INDEX (as per schema)
            $table->timestamp('deleted_at')->nullable()->index();

            // Extra metadata
            $table->json('metadata')->nullable();

            // Foreign keys
            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
