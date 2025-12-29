<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_info', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id(); // BIGINT UNSIGNED PK AUTO_INCREMENT

            $table->char('uuid', 36)
                ->unique()
                ->comment('External UUID');

            $table->string('type', 20)
                ->default('contact')
                ->comment('Dropdown: contact/social');

            $table->string('key', 60)
                ->comment('Identifier: email/phone/whatsapp/address/website/linkedin/etc.');

            $table->string('name', 120)
                ->comment('Display label (e.g., Admissions Office, Official LinkedIn)');

            $table->string('icon_class', 120)
                ->nullable()
                ->comment('FontAwesome class');

            $table->string('value', 255)
                ->comment('Actual value (email/number/text/handle/link)');

            $table->boolean('is_featured_home')
                ->default(false)
                ->comment('Show on homepage featured section');

            $table->unsignedInteger('sort_order')
                ->default(0)
                ->comment('Display order (lower comes first)');

            $table->string('status', 20)
                ->default('active')
                ->comment('active/inactive');

            $table->unsignedBigInteger('created_by')
                ->nullable()
                ->comment('Creator user ID (FK → users.id)');

            $table->timestamps(); // created_at, updated_at (nullable timestamps by default)

            $table->string('created_at_ip', 45)
                ->nullable()
                ->comment('Creation IP');

            $table->string('updated_at_ip', 45)
                ->nullable()
                ->comment('Update IP');

            // Soft delete timestamp + INDEX (as per schema)
            $table->softDeletes();
            $table->index('deleted_at');

            $table->json('metadata')
                ->nullable()
                ->comment('Extra metadata');

            // Foreign key
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_info');
    }
};
