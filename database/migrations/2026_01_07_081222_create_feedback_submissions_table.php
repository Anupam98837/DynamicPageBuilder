<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_submissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();

            /* =========================
             * Link to feedback post
             * ========================= */
            $table->unsignedBigInteger('feedback_post_id')->index(); // FK optional (keeping flexible like your posts table)

            /* =========================
             * Who submitted
             * ========================= */
            $table->unsignedBigInteger('student_id')->nullable()->index();  // submitter (usually student)
            $table->unsignedBigInteger('faculty_id')->nullable()->index();  // optional chosen faculty (if post uses faculty mapping)

            /* =========================
             * Answers JSON
             * =========================
             * Structure examples:
             *
             * 1) If faculty is selected per question (recommended):
             * {
             *   "12": {"faculty_id": 5, "stars": 4},
             *   "13": {"faculty_id": null, "stars": 5},
             *   "14": {"stars": 3}                 // faculty optional
             * }
             *
             * 2) If NO faculty at all (just stars):
             * {
             *   "12": 4,
             *   "13": 5,
             *   "14": 3
             * }
             *
             * Stars: 1..5 (you can validate in controller)
             */
            $table->json('answers')->nullable();

            /* =========================
             * Audit / meta (same DNA as yours)
             * ========================= */
            $table->string('status', 20)->default('submitted')->index(); // submitted/draft/void
            $table->timestamp('submitted_at')->nullable()->index();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            $table->timestamps();
            $table->softDeletes()->index();
            $table->json('metadata')->nullable();

            /* =========================
             * Helpful indexes / constraints
             * ========================= */
            $table->index(['feedback_post_id', 'student_id']);
            $table->index(['feedback_post_id', 'faculty_id']);

            // Optional: prevent multiple submissions by same student for same post
            $table->unique(['feedback_post_id', 'student_id'], 'uq_feedback_post_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_submissions');
    }
};
