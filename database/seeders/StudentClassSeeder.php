<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\StudentClass;
use Illuminate\Support\Str; // For string helpers like Str::startsWith
// Carbon is not strictly needed here if Eloquent handles timestamps

class StudentClassSeeder extends Seeder
{
    public function run(): void
    {
        $programs = Program::all(); // Get all programs created by ProgramSeeder

        if ($programs->isEmpty()) {
            $this->command->warn('No programs found to create student classes. Ensure ProgramSeeder ran correctly.');
            return;
        }

        foreach ($programs as $program) {
            if (Str::startsWith($program->name, 'BE in')) {
                // BE programs: Third Year and Fourth Year
                StudentClass::updateOrCreate(
                    ['code' => $program->code . '-Y3', 'program_id' => $program->id],
                    ['name' => "Third Year - {$program->name}"]
                );
                StudentClass::updateOrCreate(
                    ['code' => $program->code . '-Y4', 'program_id' => $program->id],
                    ['name' => "Fourth Year - {$program->name}"]
                );
            } elseif (Str::startsWith($program->name, 'Diploma in')) {
                // Diploma programs: First Year and Second Year
                StudentClass::updateOrCreate(
                    ['code' => $program->code . '-Y1', 'program_id' => $program->id],
                    ['name' => "First Year - {$program->name}"]
                );
                StudentClass::updateOrCreate(
                    ['code' => $program->code . '-Y2', 'program_id' => $program->id],
                    ['name' => "Second Year - {$program->name}"]
                );
            } else {
                $this->command->warn("Program '{$program->name}' (Code: {$program->code}) type (BE/Diploma) not recognized. No classes created for it.");
            }
        }

        $this->command->info('Student classes table seeded with new structure.');
    }
}