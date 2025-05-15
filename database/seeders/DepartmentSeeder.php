<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon; // Import Carbon for timestamps

// Optional: Use the model for cleaner syntax if preferred
// use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This will insert or update departments ensuring specific IDs have specific names.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Define the departments with the exact IDs and Names you specified
        $departments = [
            [
                'id'   => 1,
                'name' => 'DIT',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'   => 2,
                'name' => 'Department of humanities and management',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'   => 3,
                'name' => 'ECE',
                'created_at' => $now,
                'updated_at' => $now,
            ],
             [
                 'id'   => 4,
                 'name' => 'Mechanical',
                 'created_at' => $now,
                 'updated_at' => $now,
             ],
             [
                 'id'   => 5,
                 'name' => 'Civil engineering and surveying',
                 'created_at' => $now,
                 'updated_at' => $now,
             ],
             [
                 'id'   => 6,
                 'name' => 'Administrators', // Department for Admin roles?
                 'created_at' => $now,
                 'updated_at' => $now,
             ],
        ];

        // Use updateOrInsert to ensure idempotency (safe to re-run)
        // It matches based on 'id' and either updates the existing row or inserts the new one.
        foreach ($departments as $department) {
             DB::table('departments')->updateOrInsert(
                 ['id' => $department['id']], // Condition to find the row
                 $department                 // Data array to insert or update with
             );

            /*
            // Alternative using the Eloquent Model (if you prefer)
            // Ensure 'id' is fillable or guarded appropriately in your Department model if using this.
            // Model typically handles timestamps automatically.
            Department::updateOrCreate(
                ['id' => $department['id']], // Attributes to find the record
                ['name' => $department['name']] // Attributes to update or create
            );
            */
        }

        $this->command->info('Departments table seeded with specific IDs and names.');
    }
}