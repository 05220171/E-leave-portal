<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import DB facade

// Import your models to use them directly for truncate
use App\Models\StudentClass;
use App\Models\Program;
use App\Models\Department;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding process...');

        // --- START: ADD THIS SECTION TO CLEAR TABLES ---
        $this->command->info('Clearing relevant tables...');
        // Disable foreign key checks to avoid order issues during truncate/delete
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear tables in reverse order of dependency using Model::truncate()
        StudentClass::truncate();
        Program::truncate();
        Department::truncate();
        // Add any other tables here that these seeders might affect or depend on being clean.

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info('Tables cleared.');
        // --- END: ADD THIS SECTION TO CLEAR TABLES ---


        // Call other seeders in the correct order of dependency
        $this->call([
            DepartmentSeeder::class,    // Uses the new DepartmentSeeder
            ProgramSeeder::class,       // Uses the new ProgramSeeder
            StudentClassSeeder::class,  // Uses the new StudentClassSeeder
            // Add other seeders here if you have them
        ]);

        $this->command->info('Database seeding completed successfully with the new structure!');
    }
}