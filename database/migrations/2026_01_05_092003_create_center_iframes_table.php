<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('center_iframes', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED, PK, AUTO_INCREMENT

            $table->uuid('uuid')->unique();              // CHAR(36) UNIQUE NOT NULL
            $table->string('slug', 160)->unique();       // VARCHAR(160) UNIQUE NOT NULL

            $table->string('title', 255);                // VARCHAR(255) NOT NULL
            $table->string('iframe_url', 255);           // VARCHAR(255) NOT NULL

            $table->json('buttons_json')->nullable();    // JSON NULL (text + url + sort_order)

            $table->string('status', 20)
                ->default('active')
                ->index();                               // VARCHAR(20) DEFAULT 'active' + INDEX

            $table->timestamp('publish_at')->nullable()->index(); // TIMESTAMP NULL + INDEX
            $table->timestamp('expire_at')->nullable()->index();  // TIMESTAMP NULL + INDEX

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();                        // BIGINT UNSIGNED NULL FK -> users.id

            // created_at / updated_at (both nullable as per schema)
            $table->timestamps();

            $table->string('created_at_ip', 45)->nullable(); // VARCHAR(45) NULL
            $table->string('updated_at_ip', 45)->nullable(); // VARCHAR(45) NULL

            // deleted_at (soft delete) + index as per schema
            $table->softDeletes();
            $table->index('deleted_at');

            $table->json('metadata')->nullable();         // JSON NULL
        });
    }

    public function down(): void
    {
        Schema::table('center_iframes', function (Blueprint $table) {
            // Drop FK first (safe for rollback)
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('center_iframes');
    }
};
