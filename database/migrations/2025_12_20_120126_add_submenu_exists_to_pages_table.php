<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->enum('submenu_exists', ['yes', 'no'])
                  ->default('no')
                  ->after('department_id')
                  ->index();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['submenu_exists']); // safe even if DB ignores named index
            $table->dropColumn('submenu_exists');
        });
    }
};
