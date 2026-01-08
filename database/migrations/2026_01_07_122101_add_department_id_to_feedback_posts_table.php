<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Add required department_id FK to feedback_posts
        Schema::table('feedback_posts', function (Blueprint $table) {
            // if you want it REQUIRED, keep nullable(false)
            // put after uuid (or wherever you want)
            $table->unsignedBigInteger('department_id')->after('uuid');

            $table->index('department_id', 'feedback_posts_department_id_idx');

            // FK -> departments.id (required)
            $table->foreign('department_id', 'feedback_posts_department_id_fk')
                ->references('id')->on('departments')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('feedback_posts', function (Blueprint $table) {
            // drop FK + index + column
            $table->dropForeign('feedback_posts_department_id_fk');
            $table->dropIndex('feedback_posts_department_id_idx');
            $table->dropColumn('department_id');
        });
    }
};
