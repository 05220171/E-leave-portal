<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Program;
use App\Models\StudentClass; // If you created StudentClass model
use Illuminate\Http\Request;

class DependentDropdownController extends Controller
{
    public function getPrograms(Department $department)
    {
        // Ensure user is authorized to access this if needed, e.g.
        // if (!auth()->user()->hasRole('superadmin')) { abort(403); }

        // Fetch programs for the given department, selecting only what's needed for the dropdown
        $programs = $department->programs()->select('id', 'name', 'code')->get();
        return response()->json($programs);
    }

    public function getClasses(Program $program)
    {
        // Ensure user is authorized
        // if (!auth()->user()->hasRole('superadmin')) { abort(403); }

        // Fetch classes for the given program
        $classes = $program->studentClasses()->select('id', 'name', 'code')->get(); // Using studentClasses() relationship
        return response()->json($classes);
    }
}