<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_semesters', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT UNSIGNED PK AI

            $table->uuid('uuid')->unique(); // External UUID

            // Parent relations
            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete(); // Parent course (required)

            // Optional department override/filter
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            // Semester meta
            $table->unsignedTinyInteger('semester_no'); // 1,2,3...
            $table->string('title', 255)->nullable();   // "Semester 1"
            $table->longText('description')->nullable(); // HTML allowed
            $table->unsignedInteger('total_credits')->nullable()->default(0);
            $table->string('syllabus_url', 255)->nullable();

            $table->unsignedInteger('sort_order')->default(0); // display order
            $table->string('status', 20)->default('active');   // active/inactive

            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expire_at')->nullable();

            // Creator user ID
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // IPs
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Timestamps + Soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Extra metadata
            $table->json('metadata')->nullable();

            // Indexes (as per schema)
            $table->index('course_id');
            $table->index('department_id');
            $table->index('semester_no');
            $table->index('sort_order');
            $table->index('status');
            $table->index('publish_at');
            $table->index('expire_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_semesters');
    }
};
