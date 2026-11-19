<?php

// PHP start tag.

// A migration is a set of instructions that creates or changes database tables.
use Illuminate\Database\Migrations\Migration;
// Blueprint is the list of columns we want.
use Illuminate\Database\Schema\Blueprint;
// Schema runs those instructions against SQLite, MySQL, or another database.
use Illuminate\Support\Facades\Schema;

// The anonymous class is the migration itself. Laravel runs up() and can undo it with down().
return new class extends Migration
{
    // up() runs when we execute php artisan migrate.
    public function up(): void
    {
        // Create the enquiries table. The model Enquiry uses this table automatically.
        Schema::create('enquiries', function (Blueprint $table) {
            // id() is an auto-incrementing primary key named id.
            $table->id();
            // The Fake Store product number. It is not a foreign key because products are not stored here.
            $table->unsignedInteger('product_id');
            // A copy of the title at the time of the enquiry.
            $table->string('product_title');
            // The customer's name.
            $table->string('name');
            // The customer's email address.
            $table->string('email');
            // Phone is limited to 30 characters, matching the form rule.
            $table->string('phone', 30);
            // text() allows a longer message than string().
            $table->text('message');
            // created_at and updated_at columns.
            $table->timestamps();
        });
    }

    // down() runs when we roll this migration back. It deletes the table.
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
