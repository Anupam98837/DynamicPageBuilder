<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactUsTable extends Migration
{
    public function up()
    {
        Schema::create('contact_us', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->longText('message');

            // Optional: store google map url/embed
            $table->string('map_url', 2000)->nullable();

            // ✅ Fix for your error (controller inserting is_read)
            $table->boolean('is_read')->default(false);

            // Optional but useful for admin/audit
            $table->timestamp('read_at')->nullable();
            $table->string('created_at_ip', 45)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contact_us');
    }
}
