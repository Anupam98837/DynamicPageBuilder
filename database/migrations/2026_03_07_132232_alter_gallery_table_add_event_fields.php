<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery', function (Blueprint $table) {
            $table->string('event_title')->nullable();
            $table->text('event_description')->nullable();
            $table->date('event_date')->nullable();
            $table->string('event_shortcode')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('gallery', function (Blueprint $table) {
            $table->dropColumn([
                'event_title',
                'event_description',
                'event_date',
                'event_shortcode',
            ]);
        });
    }
};