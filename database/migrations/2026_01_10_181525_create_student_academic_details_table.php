<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_academic_details', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // External UUID
            $table->char('uuid', 36)->unique();

            // Core relations
            $table->unsignedBigInteger('user_id')->unique(); // student user (one row per user)
            $table->unsignedBigInteger('department_id')->index();
            $table->unsignedBigInteger('course_id')->index();

            // Nullable relations
            $table->unsignedBigInteger('semester_id')->nullable()->index();
            $table->unsignedBigInteger('section_id')->nullable()->index();

            // Academic fields
            $table->string('academic_year', 20)->nullable()->index(); // e.g. 2025-26 / 2025–26
            $table->unsignedSmallInteger('year')->nullable()->index(); // e.g. 2026

            // Numbers / identifiers
            $table->string('roll_no', 60)->nullable()->unique();
            $table->string('registration_no', 80)->nullable()->unique();
            $table->string('admission_no', 80)->nullable()->unique();

            $table->date('admission_date')->nullable();

            $table->string('batch', 40)->nullable()->index();   // e.g. 2023-2026
            $table->string('session', 40)->nullable()->index(); // e.g. 2025-26

            // Status
            $table->string('status', 20)->default('active')->index(); // active/inactive/passed-out

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();

            // Extra data
            $table->json('metadata')->nullable();

            // Timestamps + soft deletes
            $table->timestamps();
            $table->softDeletes();

            /* =========================
             * Foreign Keys
             * ========================= */

            // user_id -> users.id (student)
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // department_id -> departments.id
            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // course_id -> courses.id
            $table->foreign('course_id')
                ->references('id')->on('courses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // semester_id -> course_semesters.id
            $table->foreign('semester_id')
                ->references('id')->on('course_semesters')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // section_id -> course_semester_sections.id
            $table->foreign('section_id')
                ->references('id')->on('course_semester_sections')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // created_by -> users.id
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            /*
             * NOTE:
             * You mentioned: roll_no should be unique within
             * (academic_year + department_id + course_id + semester_id + section_id(optional)).
             * MySQL cannot do "optional column" uniqueness perfectly without generated columns.
             * If you want strict scoped uniqueness instead of global unique roll_no,
             * remove ->unique() from roll_no above and use a composite unique like below:
             *
             * $table->unique(['academic_year','department_id','course_id','semester_id','section_id','roll_no'], 'sad_roll_scope_unique');
             */
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_academic_details');
    }
};
