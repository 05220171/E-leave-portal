@extends('layouts.app')

@section('title', 'Manage Staff - Super Admin')

@section('css')
<style>
    /* Assuming these styles are now GLOBAL (e.g., in student.css or admin-common.css) */
    /* If not, you'd need to copy them here or move them globally */
    .page-section-title { font-size: 1.75rem; font-weight: 600; color: #2c3e50; }

    /* Action Buttons - Reusing classes from your previous example */
    .action-btn-exact {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 5px 10px; border-radius: 4px; font-size: 0.85rem;
        line-height: 1; font-weight: 500; cursor: pointer; text-decoration: none;
        vertical-align: middle; margin: 0;
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
        border: 1px solid transparent; color: #fff;
    }
    .action-btn-exact i { margin-right: 0.35rem; }
    a.action-btn-exact.edit-exact { background-color: #3498db; border-color: #3498db; }
    a.action-btn-exact.edit-exact:hover { background-color: #2980b9; border-color: #217dbb; }
    button.action-btn-exact.delete-exact { background-color: #e74c3c; border-color: #e74c3c; }
    button.action-btn-exact.delete-exact:hover { background-color: #c0392b; border-color: #b33426; }

    /* Other styles from your provided CSS for consistency */
    .d-inline-block { display: inline-block !important; }
    .me-1 { margin-right: 0.35rem !important; }
    .custom-alert { /* ... your custom alert styles ... */ }
    .custom-table-wrapper { /* ... your custom table wrapper styles ... */ }
    .custom-data-table { /* ... your custom data table styles ... */ }
    .custom-data-table thead th { /* ... */ }
    .custom-data-table tbody td { /* ... */ }
    /* ... etc. for all custom styles you want to apply ... */
    .pagination-wrapper .pagination { /* ... your pagination styles ... */ }

    /* Styles for Bootstrap d-flex search bar (if not globally available from Bootstrap) */
    .d-flex .form-control { /* ... minimal styling if Bootstrap handles most of it ... */ }
    .d-flex .btn-outline-primary { /* ... */ }
    /* ... */

</style>
@endsection

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-section-title text-start mb-0"><i class="fas fa-user-tie me-2"></i>Manage Staff</h1>
        {{-- Optional: Add button to create new staff if different from general user creation --}}
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
    <form method="GET" action="{{ route('superadmin.users.staff') }}" class="mb-4" role="search">
        <div class="d-flex">
            <input class="form-control me-2 form-control-lg" type="search" name="search"
                   placeholder="Search by Name, Email, Role, Department..."
                   value="{{ request('search') }}" aria-label="Search Staff">
            <button class="btn btn-outline-primary btn-lg" type="submit" title="Search Staff">
                <i class="fas fa-search me-1"></i>Search
            </button>
        </div>
        @if(request('search'))
            <div class="mt-2 text-start">
                <a href="{{ route('superadmin.users.staff') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times-circle me-1"></i>Clear Search
                </a>
            </div>
        @endif
    </form>
    {{-- END OF SEARCH FORM --}}

    <h3 class="mb-3 mt-4" style="font-weight: 600; color: #2980b9;">
        <i class="fas fa-list me-2"></i> Staff List
    </h3>

    <div class="custom-table-wrapper">
        <table id="staffTable" class="custom-data-table">
            <thead>
                <tr>
                    <th>Sl No.</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Registered At</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($staffs as $index => $user)
                    <tr>
                        <td>{{ $staffs->firstItem() + $index }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ Str::title($user->role) }}</td>
                        <td>{{ $user->department->name ?? 'N/A' }}</td>
                        <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                        <td class="actions-cell text-center">
                            <a href="{{ route('superadmin.users.edit', $user->id) }}" class="action-btn-exact edit-exact me-1" title="Edit Staff Member">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <form action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this staff member? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn-exact delete-exact" title="Delete Staff Member">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-3">
                             <div class="custom-alert custom-alert-info my-0">
                                @if(request('search'))
                                    No staff members found matching your search criteria.
                                @else
                                    No staff members found.
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($staffs->hasPages())
        <div class="mt-4 d-flex justify-content-center pagination-wrapper">
            {{ $staffs->links() }}
        </div>
    @endif
</div>
@endsection