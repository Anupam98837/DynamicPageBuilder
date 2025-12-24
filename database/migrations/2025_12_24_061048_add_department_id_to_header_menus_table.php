<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Safety: avoid errors if column already exists
        if (!Schema::hasColumn('header_menus', 'department_id')) {
            Schema::table('header_menus', function (Blueprint $table) {
                // Optional department link
                $table->unsignedBigInteger('department_id')->nullable()->after('parent_id');

                // Index + FK
                $table->index('department_id');
                $table->foreign('department_id')
                      ->references('id')
                      ->on('departments')
                      ->nullOnDelete()     // ON DELETE SET NULL (because optional)
                      ->cascadeOnUpdate(); // ON UPDATE CASCADE
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('header_menus', 'department_id')) {
            Schema::table('header_menus', function (Blueprint $table) {
                // Drop FK first, then index, then column
                $table->dropForeign(['department_id']);
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            });
        }
    }
};
