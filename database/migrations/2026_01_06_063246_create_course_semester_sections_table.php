<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_semester_sections', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Primary key
            $table->id(); // BIGINT UNSIGNED, PK, AUTO_INCREMENT

            // Identifiers
            $table->uuid('uuid')->unique(); // CHAR(36) UNIQUE

            // Relations
            $table->unsignedBigInteger('semester_id');            // FK -> course_semesters.id (required)
            $table->unsignedBigInteger('course_id')->nullable();  // FK -> courses.id (optional)
            $table->unsignedBigInteger('department_id')->nullable(); // FK -> departments.id (optional)

            // Content
            $table->string('title', 255);          // Section title
            $table->longText('description')->nullable(); // HTML allowed

            // Ordering / visibility
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status', 20)->default('active')->index(); // active/inactive
            $table->timestamp('publish_at')->nullable()->index();

            // Audit
            $table->unsignedBigInteger('created_by')->nullable(); // FK -> users.id
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete + timestamps
            $table->softDeletes()->index(); // deleted_at
            $table->timestamps();           // created_at, updated_at

            // Extra
            $table->json('metadata')->nullable();

            // Indexes
            $table->index('semester_id');
            $table->index('course_id');
            $table->index('department_id');

            // Foreign Keys
            $table->foreign('semester_id')
                ->references('id')->on('course_semesters')
                ->cascadeOnDelete();

            $table->foreign('course_id')
                ->references('id')->on('courses')
                ->nullOnDelete();

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
        Schema::dropIfExists('course_semester_sections');
    }
};
