<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages_submenu', function (Blueprint $table) {
            // ✅ Add department_id (nullable so existing rows don't break)
            $table->unsignedBigInteger('department_id')
                  ->nullable()
                  ->after('page_id')
                  ->index();

            // ✅ FK: department_id -> departments.id
            $table->foreign('department_id')
                  ->references('id')
                  ->on('departments')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('pages_submenu', function (Blueprint $table) {
            // drop FK first, then column
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
