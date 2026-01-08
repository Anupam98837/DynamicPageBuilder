<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->bigIncrements('id');            // BIGINT UNSIGNED PK AI
            $table->uuid('uuid')->unique();         // External UUID (CHAR(36))

            // Optional department filter
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            // Master fields
            $table->string('subject_code', 50)->index();   // e.g. CS101
            $table->string('title', 255);                  // e.g. Data Structures
            $table->string('short_title', 120)->nullable(); // e.g. DSA
            $table->longText('description')->nullable();    // HTML allowed

            /**
             * ✅ Dynamic type (NO restriction)
             * You can send: theory/practical/lab/elective/minor/major/anything
             */
            $table->string('subject_type', 50)->nullable()->index();

            // Optional meta
            $table->unsignedInteger('credits')->nullable()->default(0);
            $table->unsignedInteger('lecture_hours')->nullable()->default(0);
            $table->unsignedInteger('practical_hours')->nullable()->default(0);

            // Ordering + status
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status', 20)->default('active')->index();

            $table->timestamp('publish_at')->nullable()->index();
            $table->timestamp('expire_at')->nullable()->index();

            // Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Timestamps + Soft deletes
            $table->timestamps();
            $table->softDeletes()->index();

            // Extra metadata
            $table->json('metadata')->nullable();

            // Recommended unique constraint (code unique per department)
            $table->unique(['department_id', 'subject_code'], 'subjects_dept_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
