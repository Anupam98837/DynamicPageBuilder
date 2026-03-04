<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meta_tags', function (Blueprint $table) {
            // add after id (or change placement as you want)
            $table->unsignedBigInteger('page_id')
                  ->nullable()
                  ->after('id')
                  ->index();

            $table->foreign('page_id')
                  ->references('id')
                  ->on('pages')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('meta_tags', function (Blueprint $table) {
            $table->dropForeign(['page_id']);
            $table->dropColumn('page_id');
        });
    }
};