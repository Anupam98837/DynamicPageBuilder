<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            // Primary Key
            $table->bigIncrements('id');

            /**
             * Self FK: parent_id
             * - NULL  => this module is a root/parent node
             * - value => this module is a child of another module (modules.id)
             */
            $table->unsignedBigInteger('parent_id')->nullable();

            // UUID (UNIQUE)
            $table->char('uuid', 36)->unique();

            // Name (UNIQUE globally; you can change to unique per parent if needed)
            $table->string('name', 150)->unique();

            // Href (required, no default)
            $table->string('href', 255);

            // Optional description
            $table->text('description')->nullable();

            // Status with default 'Active'
            $table->string('status', 20)->default('Active');

            // Timestamps with proper defaults
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')
                  ->useCurrent()
                  ->useCurrentOnUpdate();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_at_ip', 45)->nullable();

            // Soft delete timestamp
            $table->timestamp('deleted_at')->nullable();

            // Indexes
            $table->index('created_by');
            $table->index('parent_id');

            // Foreign key: created_by -> users.id
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            // Self foreign key: parent_id -> modules.id
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('modules')
                  ->nullOnDelete();
            /**
             * If you want to delete children when parent is deleted instead:
             * ->cascadeOnDelete();
             */
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            // Drop FKs first to avoid constraint errors
            if (Schema::hasColumn('modules', 'parent_id')) {
                $table->dropForeign(['parent_id']);
            }
            if (Schema::hasColumn('modules', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });

        Schema::dropIfExists('modules');
    }
};
