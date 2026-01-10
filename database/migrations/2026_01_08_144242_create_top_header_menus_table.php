<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('top_header_menus', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // External UUID (unique)
            $table->char('uuid', 36)->unique();

            // Optional dept; FK -> departments.id | ON DELETE SET NULL | ON UPDATE CASCADE (indexed)
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            /**
             * ✅ NEW:
             * item_type: menu | contact
             * contact_info_id: only used when item_type = contact
             */
            $table->string('item_type', 20)->default('menu')->index(); // menu|contact
            $table->unsignedBigInteger('contact_info_id')->nullable()->index();
            $table->foreign('contact_info_id')
                ->references('id')->on('contact_info')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // ✅ Prevent duplicate contact row for same contact_info_id
            $table->unique(['item_type', 'contact_info_id'], 'thm_item_type_contact_unique');

            // Core fields
            $table->string('title', 150);
            $table->text('description')->nullable();

            // Human-readable unique slug (auto-generated in app)
            $table->string('slug', 160)->unique();

            // Optional shortcode; must be unique if present
            $table->string('shortcode', 100)->nullable()->unique();

            // Optional absolute/relative URL
            $table->string('page_url', 255)->nullable();

            // Optional page slug / shortcode; unique if present
            $table->string('page_slug', 160)->nullable()->unique();
            $table->string('page_shortcode', 100)->nullable()->unique();

            // ✅ REMOVED:
            // $table->json('contact_info_ids_json')->nullable();

            // Ordering + visibility
            $table->unsignedInteger('position')->default(0);
            $table->boolean('active')->default(true)->index();

            // Timestamps with DEFAULT CURRENT_TIMESTAMP (and auto-update)
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Soft deletes (timestamp + index)
            $table->softDeletes();

            // Audit columns (no FK enforced)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Extra metadata (we’ll use this to store a snapshot for contact rows if you want)
            $table->json('metadata')->nullable();

            // Index for ordering within department scope
            $table->index(['department_id', 'position'], 'thm_dept_position_idx');

            // Helpful filter index
            $table->index(['item_type', 'position'], 'thm_type_position_idx');
        });
    }

    public function down(): void
    {
        Schema::table('top_header_menus', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['contact_info_id']);
        });

        Schema::dropIfExists('top_header_menus');
    }
};
