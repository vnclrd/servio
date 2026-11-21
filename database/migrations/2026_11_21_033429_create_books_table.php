<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Sets up and creates table schemas in the database
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->increments('book_id'); // Set Book ID as an auto-incrementing primary key
            $table->string('title', 255); // Set book title to 255 max characters
            $table->string('author', 255); // Set book author to 255 max characters
            $table->enum('status', ['Available', 'Borrowed'])->default('Available'); // Set book statuses to Available and Borrowed only and make Available as default
            $table->timestamps(); // Sets created_at and updated_at in the db

            // Indexes speed up search lookups and status filtering
            $table->index('title'); // Creates db index on the title column
            $table->index('author'); // Creates db index on the author column
            $table->index('status'); // Creates db index on the status column
        });
    }

    /**
     * Reverse the migrations.
     */
    // Drops the schemas if they already exist in the database
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
