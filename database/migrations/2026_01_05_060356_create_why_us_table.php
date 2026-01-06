<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('why_us', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT UNSIGNED PK AI

            $table->char('uuid', 36)->unique()->comment('External UUID');

            $table->string('title', 255)->comment('Title (e.g., "Why Choose Us")');
            $table->string('slug', 160)->unique()->comment('Human-readable unique slug');

            $table->longText('body')->comment('Full content (HTML allowed)');

            $table->string('cover_image', 255)->nullable()->comment('Cover image path');
            $table->json('attachments_json')->nullable()->comment('Optional: multiple attachments list');

            $table->tinyInteger('is_featured_home')->default(0)->comment('Show on homepage featured section');

            $table->string('status', 20)->default('draft')->comment('draft/published/archived');

            $table->timestamp('publish_at')->nullable()->comment('When it becomes visible');
            $table->timestamp('expire_at')->nullable()->comment('Optional expiry time');

            $table->unsignedBigInteger('views_count')->default(0)->comment('Optional analytics counter');

            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user ID');
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // created_at, updated_at (TIMESTAMP, nullable)
            $table->timestamps();

            $table->string('created_at_ip', 45)->nullable()->comment('Creation IP');
            $table->string('updated_at_ip', 45)->nullable()->comment('Update IP');

            // deleted_at (TIMESTAMP nullable + INDEX)
            $table->timestamp('deleted_at')->nullable()->index()->comment('Soft delete timestamp');

            $table->json('metadata')->nullable()->comment('Extra metadata');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('why_us');
    }
};
