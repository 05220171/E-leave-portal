{{-- File: resources/views/superadmin/leave_types/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Leave Types</h1>
        <a href="{{ route('superadmin.leave-types.create') }}" class="btn btn-primary">
            Add New Leave Type
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Leave Type List</h6>
        </div>
        <div class="card-body">
            @if($leaveTypes->isEmpty())
                <div class="text-center">
                    <p class="lead">No leave types found.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="leaveTypesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th style="width: 220px;">Actions</th> {{-- Adjusted width for new button --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveTypes as $leaveType)
                                <tr>
                                    <td>{{ $leaveType->id }}</td>
                                    <td>{{ $leaveType->name }}</td>
                                    <td>{{ Str::limit($leaveType->description, 50) ?: 'N/A' }}</td>
                                    <td>
                                        @if($leaveType->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- VVVVVV ADD THIS NEW BUTTON VVVVVV --}}
                                        <a href="{{ route('superadmin.leave-types.workflows.index', $leaveType->id) }}" class="btn btn-sm btn-info me-1" title="Manage Workflow">
                                            Workflow
                                        </a>
                                        {{-- ^^^^^^ END OF NEW BUTTON ^^^^^^ --}}
                                        <a href="{{ route('superadmin.leave-types.edit', $leaveType->id) }}" class="btn btn-sm btn-warning me-1" title="Edit">
                                            Edit
                                        </a>
                                        <form action="{{ route('superadmin.leave-types.destroy', $leaveType->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this leave type? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($leaveTypes->hasPages())
        <div class="card-footer">
            {{ $leaveTypes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection