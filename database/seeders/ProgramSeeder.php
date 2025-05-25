<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Program;
// Carbon is not strictly needed here if Eloquent handles timestamps

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $departmentPrograms = [
            'Department of Civil engineering and surveying' => [
                ['code' => 'BESGI', 'name' => 'BE in surveying and geo-informatics'],
                ['code' => 'DCIVE', 'name' => 'Diploma in civil engineering'],
                ['code' => 'DCONS', 'name' => 'Diploma in construction and supervision'],
                ['code' => 'DSURV', 'name' => 'Diploma in surveying'],
            ],
            'Department of electrical and electronics engineering' => [
                ['code' => 'BEPEN', 'name' => 'BE in power engineering'],
                ['code' => 'DELEC', 'name' => 'Diploma in electrical engineering'],
                ['code' => 'DECE',  'name' => 'Diploma in electronics and communication engineering'],
            ],
            'Department of mechanical engineering' => [
                ['code' => 'BEMEC', 'name' => 'BE in mechanical engineering'],
                ['code' => 'DMEC',  'name' => 'Diploma in mechanical engineering'],
            ],
            'Department of information technology' => [
                ['code' => 'DCSN', 'name' => 'Diploma in computer system and network'],
                ['code' => 'DMMA', 'name' => 'Diploma in multimedia and animation'],
            ],
            'Department of humanities and management' => [
                ['code' => 'DMPM', 'name' => 'Diploma in materials and procurement management'],
            ],
        ];

        foreach ($departmentPrograms as $deptName => $programs) {
            $department = Department::where('name', $deptName)->first();

            if (!$department) {
                $this->command->warn("Department '{$deptName}' not found during ProgramSeeder. Skipping its programs. (Ensure DepartmentSeeder ran correctly)");
                continue;
            }

            foreach ($programs as $programData) {
                Program::updateOrCreate(
                    // 1st array: Attributes to find the record by (unique program code)
                    ['code' => $programData['code']],
                    // 2nd array: Attributes to update or create with
                    [
                        'name' => $programData['name'],
                        'department_id' => $department->id,
                        // Eloquent will handle created_at/updated_at automatically
                    ]
                );
            }
        }

        $this->command->info('Programs table seeded with new structure.');
    }
}