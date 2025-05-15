<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::all();
        return view('superadmin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('superadmin.create-department');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:departments,name']);
        Department::create($request->only('name'));

        return redirect()->route('superadmin.departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return view('superadmin.edit-department', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate(['name' => 'required|unique:departments,name,' . $department->id]);
        $department->update($request->only('name'));

        return redirect()->route('superadmin.departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('superadmin.departments.index')->with('success', 'Department deleted successfully.');
    }
}
