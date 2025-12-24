<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // External UUID
            $table->char('uuid', 36)->unique();

            // Optional: department-specific announcement
            $table->unsignedBigInteger('department_id')->nullable();
            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->nullOnDelete();

            // Core fields
            $table->string('title', 255);
            $table->string('slug', 160)->unique(); // human-readable unique slug
            $table->longText('body');              // HTML allowed

            // Media / attachments
            $table->string('cover_image', 255)->nullable();
            $table->json('attachments_json')->nullable();

            // Flags / status
            $table->tinyInteger('is_featured_home')->default(0);
            $table->string('status', 20)->default('draft'); // draft/published/archived

            // Visibility window
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expire_at')->nullable();

            // Analytics
            $table->unsignedBigInteger('views_count')->default(0);

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            // Created/Updated timestamps (nullable as per schema)
            $table->nullableTimestamps();

            // IP tracking
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete (with index as per schema)
            $table->softDeletes();
            $table->index('deleted_at');

            // Extra metadata
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
