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
        // 1️⃣ Rename table
        Schema::rename('department_pages', 'pages');
 
        // 2️⃣ Add nullable department_id + FK
        Schema::table('pages', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')
                  ->nullable()
                  ->after('id')
                  ->index();
 
            $table->foreign('department_id')
                  ->references('id')
                  ->on('departments')
                  ->nullOnDelete();
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1️⃣ Drop FK + column
        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
 
        // 2️⃣ Rename table back
        Schema::rename('pages', 'department_pages');
    }
};