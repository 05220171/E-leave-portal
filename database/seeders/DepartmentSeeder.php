<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department; // Use the model
// Carbon is not strictly needed here if Eloquent handles timestamps and you don't set them manually

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departmentsData = [
            ['name' => 'Department of Civil engineering and surveying'],
            ['name' => 'Department of electrical and electronics engineering'],
            ['name' => 'Department of mechanical engineering'],
            ['name' => 'Department of information technology'],
            ['name' => 'Department of humanities and management'],
            // ['name' => 'Administrators'], // Uncomment if needed for other purposes
        ];

        foreach ($departmentsData as $deptData) {
            Department::updateOrCreate(
                ['name' => $deptData['name']], // Condition to find the row (unique name)
                $deptData                      // Data array to insert or update with
                                               // Timestamps will be handled by Eloquent automatically
            );
        }

        $this->command->info('Departments table seeded with new structure.');
    }
}