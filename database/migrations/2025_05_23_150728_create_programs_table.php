<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This method defines the schema for the 'programs' table.
     * Each program will be associated with a department.
     */
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            // Primary Key: Auto-incrementing 'id'
            $table->id();

            // Foreign Key: 'department_id'
            // This links each program to a specific department in the 'departments' table.
            // - constrained(): Automatically determines the referenced table ('departments') and column ('id')
            //                  based on Laravel's naming conventions.
            // - onDelete('cascade'): If a department is deleted, all associated programs
            //                        will also be automatically deleted. Choose this behavior carefully.
            //                        Alternatives include onDelete('restrict') (prevents department deletion
            //                        if programs are linked) or onDelete('set null') (sets department_id to NULL
            //                        if programs are linked, requires the column to be nullable).
            //                        'cascade' is common if programs cannot exist without a department.
            $table->foreignId('department_id')
                  ->constrained() // Assumes 'departments' table and 'id' column
                  ->onDelete('cascade'); // Or 'restrict', 'set null' (if nullable)

            // Program Name: The full, human-readable name of the program.
            // e.g., "Diploma in Computer Systems and Networks"
            $table->string('name');

            // Program Code: A shorter, unique identifier for the program.
            // This is often used as the value in dropdowns and for internal referencing.
            // e.g., "DCSN", "DMPM"
            // - unique(): Ensures that each program code is unique across the table.
            $table->string('code')->unique();

            // Timestamps: 'created_at' and 'updated_at' columns.
            // These are automatically managed by Eloquent.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method defines how to undo the changes made by the 'up' method.
     * It will drop the 'programs' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};