<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_carousel', function (Blueprint $table) {
            // PK
            $table->bigIncrements('id');

            // External UUID
            $table->uuid('uuid')->unique();

            // Content
            $table->string('title', 255)->nullable();              // nullable (admin title)
            $table->string('slug', 160)->unique();                 // unique, not null
            $table->string('image_url', 255);                      // not null (desktop)
            $table->string('mobile_image_url', 255)->nullable();   // nullable (mobile)
            $table->longText('overlay_text')->nullable();          // nullable (HTML allowed / long text)
            $table->string('alt_text', 255)->nullable();           // nullable (accessibility alt)

            // Ordering & visibility
            $table->unsignedInteger('sort_order')->default(0)->index();   // default 0, indexed
            $table->string('status', 20)->default('draft')->index();      // draft/published/archived, indexed
            $table->timestamp('publish_at')->nullable()->index();         // indexed
            $table->timestamp('expire_at')->nullable()->index();          // indexed

            // Analytics
            $table->unsignedBigInteger('views_count')->default(0);

            // Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps(); // created_at, updated_at (nullable by default in Laravel)

            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete + index
            $table->softDeletes();
            $table->index('deleted_at');

            // Extra metadata
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_carousel');
    }
};
