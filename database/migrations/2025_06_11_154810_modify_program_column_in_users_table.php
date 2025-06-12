<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Add the new program_id column
            // Make it nullable initially to handle existing records.
            // It should be placed after department_id if possible for logical grouping.
            if (Schema::hasColumn('users', 'department_id')) {
                $table->foreignId('program_id')
                      ->nullable()
                      ->after('department_id')
                      ->constrained('programs') // Assumes your programs table is named 'programs'
                      ->onDelete('set null'); // Or 'restrict' if a program shouldn't be deleted if users are assigned
            } else {
                // If department_id doesn't exist, add it without 'after' or adjust as needed
                 $table->foreignId('program_id')
                      ->nullable()
                      ->constrained('programs')
                      ->onDelete('set null');
            }


            // 2. Rename the old 'program' string column (if it exists and you want to migrate data)
            // This step is crucial if you have existing user data with program names.
            if (Schema::hasColumn('users', 'program')) {
                $table->renameColumn('program', 'old_program_name_text');
                // After renaming, the 'old_program_name_text' column should ideally be made nullable too
                // if it wasn't already, to avoid issues if new users (non-students) don't have it.
                // $table->string('old_program_name_text')->nullable()->change(); // Requires doctrine/dbal
            }
        });

        // --- IMPORTANT: DATA MIGRATION STEP (Separate or Here) ---
        // If you have existing users with program names in 'old_program_name_text',
        // you need to populate 'program_id'. This is best done in a seeder
        // or a separate data migration AFTER this schema change.
        // For now, this migration only handles the schema.
        // Example of how you might do it later (NOT IN THIS SCHEMA MIGRATION):
        // \App\Models\User::whereNotNull('old_program_name_text')->get()->each(function ($user) {
        //     $program = \App\Models\Program::where('name', $user->old_program_name_text)
        //                                   ->orWhere('code', $user->old_program_name_text) // If code was stored
        //                                   ->first();
        //     if ($program) {
        //         $user->program_id = $program->id;
        //         $user->saveQuietly(); // Avoids firing events if not needed
        //     }
        // });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'program_id')) {
                // Drop foreign key constraint first
                // Laravel's default naming: users_program_id_foreign
                $table->dropForeign(['program_id']);
                $table->dropColumn('program_id');
            }

            // Rename back if it was renamed in up()
            if (Schema::hasColumn('users', 'old_program_name_text') && !Schema::hasColumn('users', 'program')) {
                $table->renameColumn('old_program_name_text', 'program');
            }
        });
    }
};