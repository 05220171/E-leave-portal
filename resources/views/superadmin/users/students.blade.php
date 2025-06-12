@extends('layouts.app')

@section('title', 'Manage Students - Super Admin')

@section('css')
<style>
    /* Main Page Title */
    .page-section-title { font-size: 1.75rem; font-weight: 600; color: #2c3e50; }
    /* Action Buttons */
    .action-btn-exact { display: inline-flex; align-items: center; justify-content: center; padding: 5px 10px; border-radius: 4px; font-size: 0.85rem; line-height: 1; font-weight: 500; cursor: pointer; text-decoration: none; vertical-align: middle; margin: 0; transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out; border: 1px solid transparent; color: #fff; }
    .action-btn-exact i { margin-right: 0.35rem; }
    a.action-btn-exact.edit-exact { background-color: #3498db; border-color: #3498db; }
    a.action-btn-exact.edit-exact:hover { background-color: #2980b9; border-color: #217dbb; }
    button.action-btn-exact.delete-exact { background-color: #e74c3c; border-color: #e74c3c; }
    button.action-btn-exact.delete-exact:hover { background-color: #c0392b; border-color: #b33426; }
    /* Table and general styles from your provided CSS */
    .d-inline-block { display: inline-block !important; }
    .me-1 { margin-right: 0.35rem !important; } /* Ensure this is your desired spacing */
    .custom-alert { position: relative; padding: 0.75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: 0.25rem; }
    .custom-alert-success { color: #0f5132; background-color: #d1e7dd; border-color: #badbcc; }
    .custom-alert-danger { color: #842029; background-color: #f8d7da; border-color: #f5c2c7; }
    .custom-alert-info { color: #055160; background-color: #cff4fc; border-color: #b6effb; }
    .custom-alert-close { padding: 0; margin-top: -0.25rem; background: none; border: 0; float: right; font-size: 1.5rem; font-weight: 700; line-height: 1; color: #000; text-shadow: 0 1px 0 #fff; opacity: .5; cursor: pointer; }
    .custom-alert-close:hover { opacity: .75; color: #000;}
    .custom-table-wrapper { overflow-x: auto; background-color: #fff; border: 1px solid #dee2e6; border-radius: 0.375rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); margin-bottom: 1.5rem; }
    .custom-data-table { width: 100%; margin-bottom: 1rem; color: #212529; vertical-align: top; border-color: #dee2e6; border-collapse: collapse; }
    .custom-data-table th, .custom-data-table td { padding: 0.75rem; vertical-align: middle; border-bottom: 1px solid #dee2e6; }
    .custom-data-table thead th { vertical-align: bottom; border-bottom-width: 2px; background-color: #f8f9fa; font-weight: 600; color: #495057; text-transform: uppercase; font-size: 0.85em; letter-spacing: 0.05em; }
    .custom-data-table tbody tr:last-of-type td { border-bottom-width: 0; }
    .custom-data-table tbody tr:hover { background-color: rgba(0,0,0,.035); }
    .actions-cell { white-space: nowrap; }
    .text-center { text-align: center !important; }
    .text-start { text-align: left !important; }
    .mb-0 { margin-bottom: 0 !important; }
    .mb-3 { margin-bottom: 1rem !important; }
    .mt-4 { margin-top: 1.5rem !important; }
    .py-3 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
    .my-0 { margin-top: 0 !important; margin-bottom: 0 !important; }
    .pagination-wrapper .pagination { display: flex; padding-left: 0; list-style: none; }
    .pagination-wrapper .page-item .page-link { padding: 0.375rem 0.75rem; margin-left: -1px; line-height: 1.25; color: #0d6efd; background-color: #fff; border: 1px solid #dee2e6; }
    .pagination-wrapper .page-item:first-child .page-link { margin-left: 0; border-top-left-radius: 0.25rem; border-bottom-left-radius: 0.25rem; }
    .pagination-wrapper .page-item:last-child .page-link { border-top-right-radius: 0.25rem; border-bottom-right-radius: 0.25rem; }
    .pagination-wrapper .page-item.active .page-link { z-index: 3; color: #fff; background-color: #0d6efd; border-color: #0d6efd; }
    .pagination-wrapper .page-item.disabled .page-link { color: #6c757d; pointer-events: none; background-color: #fff; border-color: #dee2e6; }
    .pagination-wrapper .page-link:hover { z-index: 2; color: #0a58ca; background-color: #e9ecef; border-color: #dee2e6; }

    /* Search Bar Specific Styles (if needed, or rely on Bootstrap) */
    /* The d-flex approach usually needs minimal custom CSS for the search bar itself */
</style>
@endsection

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-section-title text-start mb-0"><i class="fas fa-user-graduate me-2"></i>Manage Students</h1>
        {{-- Optional: Add button to create new student --}}
        <div>
             <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary btn-sm header-action-btn"> {{-- Assuming header-action-btn exists in global CSS --}}
                <i class="fas fa-user-plus me-1"></i> Add New User
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="custom-alert custom-alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="custom-alert custom-alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- SEARCH FORM --}}
    <form method="GET" action="{{ route('superadmin.users.students') }}" class="mb-4" role="search">
        <div class="d-flex">
            <input class="form-control me-2 form-control-lg" type="search" name="search"
                   placeholder="Search by Name, Email, Dept, Program, Class..."
                   value="{{ request('search') }}" aria-label="Search Students">
            <button class="btn btn-outline-primary btn-lg" type="submit" title="Search Students">
                <i class="fas fa-search me-1"></i>Search
            </button>
        </div>
        @if(request('search'))
            <div class="mt-2 text-start">
                <a href="{{ route('superadmin.users.students') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times-circle me-1"></i>Clear Search
                </a>
            </div>
        @endif
    </form>
    {{-- END OF SEARCH FORM --}}


    <h3 class="mb-3 mt-4" style="font-weight: 600; color: #2980b9;">
        <i class="fas fa-list me-2"></i> Student List
    </h3>

    <div class="custom-table-wrapper">
        <table id="studentsTable" class="custom-data-table">
            <thead>
                <tr>
                    <th>Sl No.</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Program</th>
                    <th>Class</th>
                    <th>Registered At</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $index => $user)
                    <tr>
                        <td>{{ $students->firstItem() + $index }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ Str::title($user->role) }}</td>
                        <td>{{ $user->department->name ?? 'N/A' }}</td>
                        <td>
                            @if ($user->program)
                                {{ $user->program->name }}
                                {{-- <small class="d-block text-muted">({{ $user->program->code }})</small> --}}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $user->class ?? 'N/A' }}</td>
                        <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                        <td class="actions-cell text-center">
                            <a href="{{ route('superadmin.users.edit', $user->id) }}" class="action-btn-exact edit-exact me-1" title="Edit Student">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <form action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this student? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn-exact delete-exact" title="Delete Student">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-3">
                             <div class="custom-alert custom-alert-info my-0">
                                @if(request('search'))
                                    No students found matching your search criteria.
                                @else
                                    No students found.
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($students->hasPages())
        <div class="mt-4 d-flex justify-content-center pagination-wrapper">
            {{ $students->links() }}
        </div>
    @endif
</div>
@endsection