<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'technical_assistant_preview_orders',
        'placement_officer_preview_orders',
        'faculty_preview_orders',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            // Drop FK first
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['department_id']);
            });

            // Make department_id nullable
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->change();
            });

            // Re-add FK
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('department_id')
                    ->references('id')
                    ->on('departments');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            // Prevent rollback failure if NULL values exist
            if (DB::table($tableName)->whereNull('department_id')->exists()) {
                throw new \RuntimeException(
                    "Cannot rollback {$tableName}: NULL department_id values exist. Update/delete those rows first."
                );
            }

            // Drop FK first
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['department_id']);
            });

            // Revert to NOT NULL
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable(false)->change();
            });

            // Re-add FK
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('department_id')
                    ->references('id')
                    ->on('departments');
            });
        }
    }
};