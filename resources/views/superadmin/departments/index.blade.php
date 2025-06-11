@extends('layouts.app')

@section('title', 'Manage Departments')

@section('css')
<style>
    /* Main Page Title */
    .page-section-title {
        font-size: 1.6rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0;
    }

    /* "Add New Department" Button */
    .header-action-btn {
        padding: 0.4rem 0.8rem; font-size: 0.9rem; line-height: 1.5;
        border-radius: 0.25rem; text-decoration: none; border: 1px solid transparent;
        display: inline-flex; align-items: center; justify-content: center;
        transition: all 0.2s ease-in-out; color: #fff;
        background-color: #0d6efd; /* Bootstrap 5 Primary Blue */
        border-color: #0d6efd;
    }
    .header-action-btn i { margin-right: 0.5em; }
    .header-action-btn:hover { background-color: #0b5ed7; border-color: #0a58ca; }

    /* Styling for the department list container */
    .department-list-card {
        background-color: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.07); /* Softer shadow */
        margin-top: 1.5rem;
    }

    /* Individual department item styling */
    .department-item {
        display: flex; /* Use flexbox for alignment */
        justify-content: space-between; /* Pushes name to left, actions to right */
        align-items: center; /* Vertically aligns items */
        padding: 0.9rem 1.25rem; /* Padding for each item */
        border-bottom: 1px solid #f0f0f0; /* Separator line */
        color: #333;
    }
    .department-item:last-child {
        border-bottom: none; /* No border for the last item */
    }
    .department-item:hover {
        background-color: #f9fafb; /* Slight hover effect */
    }

    .department-name {
        font-weight: 500;
        flex-grow: 1; /* Allows name to take available space */
        margin-right: 1rem; /* Space before action buttons */
    }

    .department-actions {
        white-space: nowrap; /* Keep action buttons on one line */
    }

    /* Table Action Buttons (Edit/Delete) - Consistent with screenshot */
    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.35rem 0.75rem; /* Adjust padding to match screenshot */
        border-radius: 0.25rem; /* Standard rounded corners */
        font-size: 0.875rem;
        line-height: 1.3;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        vertical-align: middle;
        border: 1px solid transparent;
        color: #fff;
        transition: background-color 0.15s ease-in-out;
    }
    .action-button i {
        margin-right: 0.35em; /* Space between icon and text */
    }
    .action-button.edit {
        background-color: #0d6efd; /* Bootstrap Primary Blue */
        border-color: #0d6efd;
        margin-right: 0.5rem; /* Space between edit and delete */
    }
    .action-button.edit:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    .action-button.delete {
        background-color: #dc3545; /* Bootstrap Danger Red */
        border-color: #dc3545;
    }
    .action-button.delete:hover {
        background-color: #bb2d3b;
        border-color: #b02a37;
    }

    /* Alert styling (if you want to override Bootstrap defaults slightly) */
    .alert { border-radius: 0.25rem; font-size: 0.9rem; }
    /* If using custom alerts from previous HOD dashboard */
    .custom-alert { position: relative; padding: 0.75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: 0.25rem; }
    .custom-alert-success { color: #0f5132; background-color: #d1e7dd; border-color: #badbcc; }
    .custom-alert-danger { color: #842029; background-color: #f8d7da; border-color: #f5c2c7; }
    .custom-alert-info { color: #052c65; background-color: #cfe2ff; border-color: #b6d4fe; }
    .custom-alert-close { float: right; font-size: 1.2rem; font-weight: 700; line-height: 1; color: inherit; text-shadow: 0 1px 0 #fff; opacity: .5; background-color: transparent; border: 0; padding: 0; cursor: pointer; }
    .custom-alert-close:hover { opacity: .75; }

</style>
@endsection


@section('content')
    <div class="container mt-4">
        {{-- Optional: Constrain width and center the content block --}}
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9"> {{-- Adjust col size for desired width --}}

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-section-title text-start">Manage Departments</h1>
                    <div>
                        <a href="{{ route('superadmin.departments.create') }}" class="header-action-btn">
                            <i class="fas fa-plus"></i> Add New Department
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="custom-alert custom-alert-success" role="alert">
                        {{ session('success') }}
                        <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="custom-alert custom-alert-danger" role="alert">
                        {{ session('error') }}
                        <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button>
                    </div>
                @endif

                @if($departments->isEmpty())
                    <div class="department-list-card mt-3">
                        <div class="department-item text-center text-muted">
                            No departments found.
                            <a href="{{ route('superadmin.departments.create') }}" class="fw-semibold text-decoration-none ms-2">Add a new department?</a>
                        </div>
                    </div>
                @else
                    <div class="department-list-card mt-3">
                        {{-- Optional Header Row (not like a table header, but a title for the list) --}}
                        {{-- You can uncomment this if you want a "header" for your list items --}}
                        {{--
                        <div class="department-item fw-bold" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <span class="department-name" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">Department Name</span>
                            <span class="department-actions" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">Actions</span>
                        </div>
                        --}}
                        @foreach($departments as $index => $department)
                            <div class="department-item">
                                <span class="department-name">
                                    <strong class="me-2">{{ $loop->iteration }}.</strong> {{ $department->name }}
                                </span>
                                <div class="department-actions">
                                    <a href="{{ route('superadmin.departments.edit', $department->id) }}" class="action-button edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('superadmin.departments.destroy', $department->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-button delete"
                                                onclick="return confirm('Are you sure you want to delete department: \'{{ addslashes($department->name) }}\'? This action cannot be undone.');">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                {{-- Pagination (if you switch to using pagination in controller) --}}
                {{-- @if($departments->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $departments->links() }}
                    </div>
                @endif --}}
            </div>
        </div>
    </div>
@endsection