@extends('layouts.app')

@section('title', 'Manage All Users - Super Admin')

@section('css')
<style>
    .page-section-title { font-size: 1.75rem; font-weight: 600; color: #2c3e50; }
    .custom-btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; line-height: 1.5; border-radius: 0.2rem; text-decoration: none; border: 1px solid transparent; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease-in-out; }
    .custom-btn-sm i.me-1 { margin-right: 0.3em; }
    .custom-btn-info { color: #fff; background-color: #1abc9c; border-color: #1abc9c; }
    .custom-btn-info:hover { background-color: #16a085; border-color: #148f77; }
    .custom-btn-primary { color: #fff; background-color: #3498db; border-color: #3498db; }
    .custom-btn-primary:hover { background-color: #2980b9; border-color: #217dbb; }

    /* ==================== MODIFIED CSS BLOCK START ==================== */
    /* Base style for action buttons */
    .action-btn-exact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 10px; /* Slightly more padding for better look */
        border-radius: 4px;
        font-size: 0.85rem;
        line-height: 1;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        vertical-align: middle;
        margin: 0;
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
        border: 1px solid transparent; /* Border is set by specific color rules */
        color: #fff; /* Text is white for both */
    }

    /* Edit Button - Blue */
    a.action-btn-exact.edit-exact {
        background-color: #3498db;
        border-color: #3498db;
    }
    a.action-btn-exact.edit-exact:hover {
        background-color: #2980b9;
        border-color: #217dbb;
    }

    /* Delete Button - Red */
    button.action-btn-exact.delete-exact {
        background-color: #e74c3c;
        border-color: #e74c3c;
    }
    button.action-btn-exact.delete-exact:hover {
        background-color: #c0392b;
        border-color: #b33426;
    }
    /* ===================== MODIFIED CSS BLOCK END ===================== */

    .d-inline-block { display: inline-block !important; }
    .me-1 { margin-right: 0.35rem !important; }
    .me-2 { margin-right: 0.5rem !important; }
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
    .pagination-wrapper .page-item .page-link { padding: 0.375rem 0.75rem; margin-left: -1px; line-height: 1.25; color: #0d6efd; background-color: #fff; border: 1px solid #dee2e6; transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
    .pagination-wrapper .page-item:first-child .page-link { margin-left: 0; border-top-left-radius: 0.25rem; border-bottom-left-radius: 0.25rem; }
    .pagination-wrapper .page-item:last-child .page-link { border-top-right-radius: 0.25rem; border-bottom-right-radius: 0.25rem; }
    .pagination-wrapper .page-item.active .page-link { z-index: 3; color: #fff; background-color: #0d6efd; border-color: #0d6efd; }
    .pagination-wrapper .page-item.disabled .page-link { color: #6c757d; pointer-events: none; background-color: #fff; border-color: #dee2e6; }
    .pagination-wrapper .page-link:hover { z-index: 2; color: #0a58ca; background-color: #e9ecef; border-color: #dee2e6; }
</style>
@endsection

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-section-title text-start mb-0">Manage All Users 👥</h1>
        <div>
            <a href="{{ route('superadmin.users.importForm') }}" class="custom-btn-sm custom-btn-info me-1">
                <i class="fas fa-upload me-1"></i> Import Users
            </a>
            <a href="{{ route('superadmin.users.create') }}" class="custom-btn-sm custom-btn-primary">
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

    <h3 class="mb-3 mt-4" style="font-weight: 600; color: #2980b9;">
        <i class="fas fa-users me-2"></i> All Users List
    </h3>

    <div class="custom-table-wrapper">
        <table id="usersTable" class="custom-data-table">
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
                @forelse ($users as $index => $user)
                    <tr>
                        <td>{{ $users->firstItem() + $index }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ Str::title($user->role) }}</td>
                        <td>{{ $user->department->name ?? 'N/A' }}</td>
                        <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                        <td class="actions-cell text-center">
                            <a href="{{ route('superadmin.users.edit', $user->id) }}" class="action-btn-exact edit-exact me-1" title="Edit User">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <form action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn-exact delete-exact" title="Delete User">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-3">
                             <div class="custom-alert custom-alert-info my-0">
                                No users found. You can add one using the button above.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="mt-4 d-flex justify-content-center pagination-wrapper">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection

@section('js')
<script>
    var alertList = document.querySelectorAll('.custom-alert.alert-dismissible');
    alertList.forEach(function (alert) {
        var closeButton = alert.querySelector('.custom-alert-close');
        if (closeButton) {
            var bootstrapAlert = window.bootstrap?.Alert?.getInstance(alert);
            if (!bootstrapAlert) {
                closeButton.addEventListener('click', function () {
                    var alertNode = this.closest('.custom-alert');
                    if (alertNode) {
                        alertNode.style.opacity = '0';
                        setTimeout(function() {
                           if (alertNode.parentNode) {
                               alertNode.parentNode.removeChild(alertNode);
                           }
                        }, 150);
                    }
                });
            }
        }
    });
</script>
@endsection