<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This method defines the schema for the 'student_classes' table.
     * Each class (or section/year level within a program) will be associated with a program.
     */
    public function up(): void
    {
        Schema::create('student_classes', function (Blueprint $table) {
            // Primary Key: Auto-incrementing 'id'
            $table->id();

            // Foreign Key: 'program_id'
            // This links each student class to a specific program in the 'programs' table.
            // - constrained(): Automatically determines the referenced table ('programs') and column ('id')
            //                  based on Laravel's naming conventions.
            // - onDelete('cascade'): If a program is deleted, all associated student classes
            //                        will also be automatically deleted. Choose this behavior carefully.
            //                        'restrict' or 'set null' (if nullable) are alternatives.
            $table->foreignId('program_id')
                  ->constrained() // Assumes 'programs' table and 'id' column
                  ->onDelete('cascade'); // Or 'restrict', 'set null' (if nullable)

            // Class Name: The full, human-readable name of the class or section.
            // e.g., "D1CSN - Year 1 Section A", "Second Year Computer Networks"
            $table->string('name');

            // Class Code: A shorter, unique identifier for the class.
            // This is often used as the value in dropdowns and for internal referencing.
            // e.g., "D1CSNA", "Y2CSN"
            // - unique(): Ensures that each class code is unique across the table.
            $table->string('code')->unique();

            // Optional: Year Level or Semester
            // You might want to explicitly store the year level or semester if it's not
            // directly inferable from the name or code, or for easier querying.
            // $table->unsignedTinyInteger('year_level')->nullable(); // e.g., 1, 2, 3
            // $table->string('semester')->nullable(); // e.g., "Fall 2024", "Spring 2025"

            // Timestamps: 'created_at' and 'updated_at' columns.
            // These are automatically managed by Eloquent.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method defines how to undo the changes made by the 'up' method.
     * It will drop the 'student_classes' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_classes');
    }
};