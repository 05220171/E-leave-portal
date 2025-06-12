<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Department; // To fetch departments for the create/edit form
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // For unique validation on update
use Illuminate\Support\Str;     // For Str::slug if needed for code generation

class ProgramController extends Controller
{
    /**
     * Display a listing of the programs.
     */
    public function index()
    {
        // Eager load the department relationship for display
        $programs = Program::with('department')->orderBy('department_id')->orderBy('name')->paginate(15);
        return view('superadmin.programs.index', compact('programs'));
    }

    /**
     * Show the form for creating a new program.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get(); // Get departments for the dropdown
        return view('superadmin.programs.create', compact('departments'));
    }

    /**
     * Store a newly created program in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:programs,code', // Ensure code is unique
            'department_id' => 'required|exists:departments,id',
        ]);

        Program::create($validated);

        return redirect()->route('superadmin.programs.index')
                         ->with('success', 'Program created successfully.');
    }

    /**
     * Display the specified program.
     * (We might not use a dedicated show page for programs, often edit is sufficient)
     */
    public function show(Program $program)
    {
        // If you have a show view:
        // $program->load('department');
        // return view('superadmin.programs.show', compact('program'));
        return redirect()->route('superadmin.programs.edit', $program->code); // Redirect to edit page
    }

    /**
     * Show the form for editing the specified program.
     * Route model binding uses 'code' because of getRouteKeyName() in Program model.
     */
    public function edit(Program $program)
    {
        $departments = Department::orderBy('name')->get();
        return view('superadmin.programs.edit', compact('program', 'departments'));
    }

    /**
     * Update the specified program in storage.
     */
    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('programs', 'code')->ignore($program->id), // Ignore current program's code
            ],
            'department_id' => 'required|exists:departments,id',
        ]);

        $program->update($validated);

        return redirect()->route('superadmin.programs.index')
                         ->with('success', 'Program updated successfully.');
    }

    /**
     * Remove the specified program from storage.
     */
    public function destroy(Program $program)
    {
        // Add check here if program is in use by users or classes before deleting
        // Example:
        // if ($program->users()->exists() || $program->studentClasses()->exists()) {
        //     return redirect()->route('superadmin.programs.index')
        //                      ->with('error', 'Cannot delete program. It is currently assigned to users or classes.');
        // }

        try {
            $program->delete();
            return redirect()->route('superadmin.programs.index')
                             ->with('success', 'Program deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Catch foreign key constraint violation or other DB errors
            return redirect()->route('superadmin.programs.index')
                             ->with('error', 'Cannot delete program. It might be in use or a database error occurred.');
        }
    }
}