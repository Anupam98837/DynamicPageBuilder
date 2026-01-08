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
        Schema::create('contact_us_page_visibility', function (Blueprint $table) {
            $table->id();

            // ✅ show / hide toggles (NO FK)
            $table->boolean('show_address')->default(true);
            $table->boolean('show_call')->default(true);
            $table->boolean('show_recruitment')->default(true);
            $table->boolean('show_email')->default(true);
            $table->boolean('show_form')->default(true);
            $table->boolean('show_map')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_us_page_visibility');
    }
};
