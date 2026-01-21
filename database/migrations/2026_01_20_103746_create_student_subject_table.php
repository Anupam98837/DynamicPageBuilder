<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_subject', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Primary key
            $table->bigIncrements('id'); // BIGINT UNSIGNED PK AUTO_INCREMENT

            // External UUID
            $table->char('uuid', 36)->unique(); // CHAR(36) UNIQUE (External UUID)

            // Required scopes
            $table->unsignedBigInteger('department_id'); // FK -> departments.id (required)
            $table->unsignedBigInteger('course_id');      // FK -> courses.id (required)

            // Nullable semester scope
            $table->unsignedBigInteger('semester_id')->nullable(); // FK -> course_semesters.id (nullable)

            // JSON array of objects (Required)
            $table->json('subject_json'); // NOT NULL

            // Status
            $table->string('status', 20)->default('active'); // active / inactive

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable(); // FK -> users.id (nullable)
            $table->string('created_at_ip', 45)->nullable();      // Creator IP address
            $table->string('updated_at_ip', 45)->nullable();      // Updater IP address

            // Laravel timestamps (nullable as per schema)
            $table->timestamps(); // created_at, updated_at (TIMESTAMP nullable)

            // Soft delete (indexed as per schema)
            $table->softDeletes(); // deleted_at TIMESTAMP nullable + index

            // Extra metadata
            $table->json('metadata')->nullable();

            /* ===========================
             | Indexes
             |=========================== */
            $table->index('department_id');
            $table->index('course_id');
            $table->index('semester_id');
            $table->index('created_by');

            /* ===========================
             | Foreign Keys
             |=========================== */
            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->cascadeOnDelete();

            $table->foreign('course_id')
                ->references('id')->on('courses')
                ->cascadeOnDelete();

            // semester_id nullable → when semester deleted, keep record but set null
            $table->foreign('semester_id')
                ->references('id')->on('course_semesters')
                ->nullOnDelete();

            // created_by nullable → if user deleted, keep record but set null
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_subject', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['course_id']);
            $table->dropForeign(['semester_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('student_subject');
    }
};
