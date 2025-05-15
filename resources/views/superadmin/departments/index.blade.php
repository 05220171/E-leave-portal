@extends('layouts.superadmin-no-sidebar')

@section('content')
    <div class="container">
        <h2>Manage Departments</h2>
        <a href="{{ route('superadmin.departments.create') }}" class="btn btn-primary">Add New Department</a>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th></th>
                    <th>Department Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Loop through departments and display them -->
                @foreach($departments as $department)
                    <tr>
                        <td>{{ $department->id }}</td>
                        <td>{{ $department->name }}</td>
                        <td>
                            <a href="{{ route('superadmin.departments.edit', $department->id) }}" class="btn btn-warning">Edit</a>
                            <form action="{{ route('superadmin.departments.destroy', $department->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
