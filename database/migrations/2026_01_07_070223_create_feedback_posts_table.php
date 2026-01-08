<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();

            /* =========================
             *  Core info
             * ========================= */
            $table->string('title', 255)->index();            // e.g. "Sem 3 Feedback (2025-26)"
            $table->string('short_title', 120)->nullable();
            $table->longText('description')->nullable();      // HTML allowed

            /* =========================
             *  Optional scoping
             *  (NO FK because table names may differ in your project)
             * ========================= */
            $table->unsignedBigInteger('course_id')->nullable()->index();
            $table->unsignedBigInteger('semester_id')->nullable()->index();
            $table->unsignedBigInteger('subject_id')->nullable()->index();  // optional; you may later add FK -> subjects.id
            $table->unsignedBigInteger('section_id')->nullable()->index();

            /**
             * Year input (choose one)
             * - academic_year: "2025-26" or "2025-2026"
             * - year: 2026 (simple numeric)
             */
            $table->string('academic_year', 20)->nullable()->index();
            $table->unsignedSmallInteger('year')->nullable()->index();

            /* =========================
             *  JSON config (as you asked)
             * ========================= */

            // [question_id, question_id, ...]
            $table->json('question_ids')->nullable();

            // [faculty_id, faculty_id, ...] (global list, optional)
            $table->json('faculty_ids')->nullable();

            /**
             * Map:
             * {
             *   "12": {"faculty_ids":[5,9]},
             *   "13": null,
             *   "14": {"faculty_ids":null}
             * }
             */
            $table->json('question_faculty')->nullable();

            // [student_id, student_id, ...]
            $table->json('student_ids')->nullable();

            /* =========================
             *  Status + visibility window
             * ========================= */
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status', 20)->default('active')->index(); // active/inactive

            $table->timestamp('publish_at')->nullable()->index();
            $table->timestamp('expire_at')->nullable()->index();

            /* =========================
             *  Audit
             * ========================= */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            /* =========================
             *  Timestamps + Soft delete + metadata
             * ========================= */
            $table->timestamps();
            $table->softDeletes()->index();
            $table->json('metadata')->nullable();

            /* =========================
             * Helpful combined indexes
             * ========================= */
            $table->index(['status', 'publish_at']);
            $table->index(['course_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_posts');
    }
};
