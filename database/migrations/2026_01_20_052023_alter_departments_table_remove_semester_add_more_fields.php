<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {

            // ✅ 1) Remove semester column (total_semesters)
            if (Schema::hasColumn('departments', 'total_semesters')) {
                $table->dropColumn('total_semesters');
            }

            // ✅ 2) Add new columns
            if (!Schema::hasColumn('departments', 'short_name')) {
                $table->string('short_name', 60)
                      ->nullable()
                      ->after('slug');
            }

            if (!Schema::hasColumn('departments', 'department_type')) {
                $table->string('department_type', 60)
                      ->nullable()
                      ->after('short_name');
            }

            if (!Schema::hasColumn('departments', 'description')) {
                $table->longText('description')
                      ->nullable()
                      ->after('department_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {

            // ✅ rollback: remove new columns
            if (Schema::hasColumn('departments', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('departments', 'department_type')) {
                $table->dropColumn('department_type');
            }

            if (Schema::hasColumn('departments', 'short_name')) {
                $table->dropColumn('short_name');
            }

            // ✅ rollback: add total_semesters back
            if (!Schema::hasColumn('departments', 'total_semesters')) {
                $table->unsignedTinyInteger('total_semesters')
                      ->default(8)
                      ->comment('Number of semesters for this department');
            }
        });
    }
};
