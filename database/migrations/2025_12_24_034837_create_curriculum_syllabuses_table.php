<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_syllabuses', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Required FK -> departments.id
            $table->unsignedBigInteger('department_id');

            // UUID
            $table->uuid('uuid')->unique();

            // Heading shown on page (e.g., "B.Tech Syllabus", "M.Tech Syllabus")
            $table->string('title', 180);

            // Slug (unique per department)
            $table->string('slug', 200);

            // PDF path (relative to /public)
            $table->string('pdf_path', 500);
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            // Sorting + active
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);

            // Meta
            $table->json('metadata')->nullable();

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->ipAddress('created_at_ip')->nullable();

            // Timestamps + soft deletes
            $table->timestamps();
            $table->softDeletes();
            $table->index('deleted_at');

            // Indexes
            $table->index('department_id');
            $table->unique(['department_id', 'slug']);

            // Foreign keys
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->cascadeOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_syllabuses');
    }
};
