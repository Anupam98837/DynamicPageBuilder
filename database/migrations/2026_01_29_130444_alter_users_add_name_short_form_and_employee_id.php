<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Optional short form of name (e.g., initials / short display)
            $table->string('name_short_form', 50)->nullable()->after('name');

            // Optional employee id
            $table->string('employee_id', 50)->nullable()->after('role_short_form');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name_short_form', 'employee_id']);
        });
    }
};
