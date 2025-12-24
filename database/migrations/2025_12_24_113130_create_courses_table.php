<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT UNSIGNED, PK, AUTO_INCREMENT

            $table->uuid('uuid')->unique(); // CHAR(36) UNIQUE (External UUID)

            // Optional: department-specific course
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete(); // FK -> departments.id

            $table->string('title', 255);           // VARCHAR(255) NOT NULL
            $table->string('slug', 160)->unique();  // VARCHAR(160) UNIQUE

            $table->string('summary', 500)->nullable(); // VARCHAR(500) NULL
            $table->longText('body');                   // LONGTEXT NOT NULL (HTML allowed)

            $table->string('cover_image', 255)->nullable(); // VARCHAR(255) NULL
            $table->json('attachments_json')->nullable();   // JSON NULL

            $table->string('program_level', 30)->default('ug');   // VARCHAR(30) DEFAULT 'ug'
            $table->string('program_type', 50)->default('degree'); // VARCHAR(50) DEFAULT 'degree'
            $table->string('mode', 30)->default('regular');        // VARCHAR(30) DEFAULT 'regular'

            $table->unsignedInteger('duration_value')->default(0); // INT UNSIGNED DEFAULT 0
            $table->string('duration_unit', 20)->default('months'); // VARCHAR(20) DEFAULT 'months'

            $table->unsignedInteger('credits')->default(0)->nullable(); // INT UNSIGNED DEFAULT 0 NULL

            $table->longText('eligibility')->nullable(); // LONGTEXT NULL (HTML allowed)
            $table->longText('highlights')->nullable();  // LONGTEXT NULL (HTML allowed)

            $table->string('syllabus_url', 255)->nullable(); // VARCHAR(255) NULL
            $table->longText('career_scope')->nullable();    // LONGTEXT NULL (HTML allowed)

            $table->boolean('is_featured_home')->default(false); // TINYINT(1) DEFAULT 0
            $table->unsignedInteger('sort_order')->default(0);    // INT UNSIGNED DEFAULT 0

            $table->string('status', 20)->default('draft'); // VARCHAR(20) DEFAULT 'draft'

            $table->timestamp('publish_at')->nullable(); // TIMESTAMP NULL
            $table->timestamp('expire_at')->nullable();  // TIMESTAMP NULL

            $table->unsignedBigInteger('views_count')->default(0); // BIGINT UNSIGNED DEFAULT 0

            // Creator user ID
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete(); // FK -> users.id

            // created_at, updated_at (nullable timestamps by default in Laravel)
            $table->timestamps();

            $table->string('created_at_ip', 45)->nullable(); // VARCHAR(45) NULL
            $table->string('updated_at_ip', 45)->nullable(); // VARCHAR(45) NULL

            // Soft delete timestamp + INDEX (as per schema)
            $table->softDeletes();
            $table->index('deleted_at');

            $table->json('metadata')->nullable(); // JSON NULL
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
