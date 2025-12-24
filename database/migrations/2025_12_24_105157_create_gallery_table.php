<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery', function (Blueprint $table) {
            // (Optional but recommended for FK support in MySQL)
            $table->engine = 'InnoDB';

            $table->bigIncrements('id');                 // BIGINT UNSIGNED, PK, AUTO_INCREMENT
            $table->char('uuid', 36)->unique();          // CHAR(36), UNIQUE, NOT NULL

            $table->unsignedBigInteger('department_id')->nullable(); // FK -> departments.id (nullable)
            $table->string('image', 255);                // VARCHAR(255), NOT NULL

            $table->string('title', 255)->nullable();    // VARCHAR(255), nullable
            $table->string('description', 500)->nullable(); // VARCHAR(500), nullable

            $table->json('tags_json')->nullable();       // JSON, nullable

            $table->boolean('is_featured_home')->default(false); // TINYINT(1), DEFAULT 0, NOT NULL
            $table->unsignedInteger('sort_order')->default(0);   // INT UNSIGNED, DEFAULT 0, NOT NULL

            $table->string('status', 20)->default('draft'); // VARCHAR(20), DEFAULT 'draft', NOT NULL

            $table->timestamp('publish_at')->nullable(); // TIMESTAMP, nullable
            $table->timestamp('expire_at')->nullable();  // TIMESTAMP, nullable

            $table->unsignedBigInteger('views_count')->default(0); // BIGINT UNSIGNED, DEFAULT 0, NOT NULL

            $table->unsignedBigInteger('created_by')->nullable();  // FK -> users.id (nullable)

            $table->timestamps(); // created_at, updated_at (nullable timestamps)

            $table->string('created_at_ip', 45)->nullable(); // VARCHAR(45), nullable
            $table->string('updated_at_ip', 45)->nullable(); // VARCHAR(45), nullable

            $table->timestamp('deleted_at')->nullable(); // TIMESTAMP, nullable (soft delete)
            $table->index('deleted_at');                 // INDEX on deleted_at (as per schema)

            $table->json('metadata')->nullable();        // JSON, nullable

            // Helpful indexes for FK columns (safe + common)
            $table->index('department_id');
            $table->index('created_by');

            // Foreign keys
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
        Schema::table('gallery', function (Blueprint $table) {
            // Drop FKs first (important for MySQL)
            $table->dropForeign(['department_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('gallery');
    }
};
