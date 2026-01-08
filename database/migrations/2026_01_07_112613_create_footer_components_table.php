<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_components', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // External UUID (CHAR(36)) + UNIQUE
            $table->char('uuid', 36)->unique();

            // Human-readable unique slug
            $table->string('slug', 160)->unique();

            // Section 1 (top row): json of title : link/url (NOT NULL)
            $table->json('section1_menu_json');

            // Section 2: pick main header menus & their submenus (nullable)
            $table->json('section2_header_menu_json')->nullable();

            // Optional override title for section 2
            $table->string('section2_title_override', 150)->nullable();

            // Section 3 (middle row links): json of title : link/url
            $table->json('section3_menu_json')->nullable();

            // Section 4 (brand area)
            $table->boolean('same_as_header')->default(false);
            $table->string('brand_logo_url', 255)->nullable();  // logo URL/path
            $table->string('brand_title', 255);                // NOT NULL
            $table->json('rotating_text_json');                // NOT NULL (array of sentences)
            $table->json('social_links_json')->nullable();     // social links array

            // Optional address/extra footer text
            $table->text('address_text')->nullable();

            // Section 5 (bottom left): buttons/links array
            $table->json('section5_menu_json')->nullable();

            // Section 5 (bottom right): copyright line (NOT NULL)
            $table->string('copyright_text', 255);

            // Active/Inactive footer config (DEFAULT 1)
            $table->tinyInteger('status')->default(1);

            // Creator user ID (FK -> users.id), nullable
            $table->unsignedBigInteger('created_by')->nullable();

            // Timestamps (nullable by default in Laravel)
            $table->timestamps();

            // IP audit fields
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete timestamp + INDEX
            $table->softDeletes();
            $table->index('deleted_at');

            // Extra metadata (future-safe)
            $table->json('metadata')->nullable();

            // FK constraint
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_components');
    }
};
