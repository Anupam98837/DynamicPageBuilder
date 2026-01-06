<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_speak', function (Blueprint $table) {
            // PK
            $table->id(); // BIGINT UNSIGNED PK AUTO_INCREMENT

            // Identifiers
            $table->char('uuid', 36)->unique();          // UNIQUE, NOT NULL
            $table->string('slug', 160)->unique();       // UNIQUE, NOT NULL

            // Optional department FK
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete(); // FK → departments.id, nullable

            // Content
            $table->string('title', 255);                // NOT NULL
            $table->longText('description')->nullable(); // NULL

            // List of iFrame/embed URLs (schema says NOT NULL)
            // (your app should always send an array/object; e.g. [{ "title": "...", "url": "..." }])
            $table->json('iframe_urls_json');            // NOT NULL

            // Carousel / UI flags
            $table->boolean('auto_scroll')->default(true);          // TINYINT(1) DEFAULT 1
            $table->unsignedInteger('scroll_latency_ms')->default(3000); // INT UNSIGNED DEFAULT 3000
            $table->boolean('loop')->default(true);                 // DEFAULT 1
            $table->boolean('show_arrows')->default(true);          // DEFAULT 1
            $table->boolean('show_dots')->default(true);            // DEFAULT 1

            // Ordering / status
            $table->unsignedInteger('sort_order')->default(0)->index();  // DEFAULT 0, INDEX
            $table->string('status', 20)->default('draft')->index();     // DEFAULT 'draft', INDEX

            // Visibility windows
            $table->timestamp('publish_at')->nullable()->index();   // INDEX, nullable
            $table->timestamp('expire_at')->nullable()->index();    // INDEX, nullable

            // Analytics
            $table->unsignedBigInteger('views_count')->default(0);  // BIGINT UNSIGNED DEFAULT 0

            // Creator (optional)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete(); // FK → users.id, nullable

            // Timestamps (both nullable as per schema)
            $table->timestamps(); // created_at, updated_at (nullable)

            // IP audit
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete (INDEX required by schema)
            $table->softDeletes();        // deleted_at nullable timestamp
            $table->index('deleted_at');  // ensure INDEX

            // Extra metadata
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_speak');
    }
};
