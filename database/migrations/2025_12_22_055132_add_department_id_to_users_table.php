<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // department_id - BIGINT UNSIGNED - Nullable - FK -> departments.id
            $table->unsignedBigInteger('department_id')
                  ->nullable()
                  ->after('role_short_form'); // adjust position if you want

            $table->foreign('department_id')
                  ->references('id')
                  ->on('departments')
                  ->nullOnDelete(); // if department deleted, set user's department_id to NULL
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop FK first, then the column
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
