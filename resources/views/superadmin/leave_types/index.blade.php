@extends('layouts.app') {{-- This layout loads student.css --}}

@section('title', 'Manage Leave Types - Super Admin')

@section('content')
<div class="container mt-4"> {{-- Bootstrap container for overall padding --}}

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-section-title text-start mb-0">Manage Leave Types</h1> {{-- Applied custom title class, text-start --}}
        <a href="{{ route('superadmin.leave-types.create') }}" class="custom-btn custom-btn-primary"> {{-- Custom button class --}}
            <i class="fas fa-plus me-1"></i> Add New Leave Type
        </a>
    </div>

    @if(session('success'))
        <div class="custom-alert custom-alert-success" role="alert"> {{-- Custom alert --}}
            {{ session('success') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button>
        </div>
    @endif
    @if(session('error'))
        <div class="custom-alert custom-alert-danger" role="alert"> {{-- Custom alert --}}
            {{ session('error') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button>
        </div>
    @endif

    {{-- Section title similar to HOD's "Pending Approvals" --}}
    <h3 class="mb-3 mt-4" style="font-weight: 600; color: #2980b9;">
        <i class="fas fa-list-alt me-2"></i> Leave Type List
    </h3>

    <div class="custom-table-wrapper"> {{-- Your custom table wrapper --}}
        <table class="custom-data-table" id="leaveTypesTable"> {{-- Your custom data table class --}}
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th style="min-width: 250px; text-align: center;">Actions</th> {{-- Adjusted width --}}
                </tr>
            </thead>
            <tbody>
                @if($leaveTypes->isEmpty())
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="py-3">No leave types found. You can add one using the button above.</div>
                        </td>
                    </tr>
                @else
                    @foreach($leaveTypes as $leaveType)
                        <tr>
                            <td>{{ $leaveType->id }}</td>
                            <td>{{ $leaveType->name }}</td>
                            <td>{{ Str::limit($leaveType->description, 70) ?: 'N/A' }}</td>
                            <td>
                                @if($leaveType->is_active)
                                    <span class="status-badge status-approved">Active</span> {{-- Custom status badge --}}
                                @else
                                    <span class="status-badge status-cancelled">Inactive</span> {{-- Custom status badge --}}
                                @endif
                            </td>
                            <td class="actions-cell text-center"> {{-- Use actions-cell for consistent button margin --}}
                                <a href="{{ route('superadmin.leave-types.workflows.index', $leaveType->id) }}" class="custom-btn-sm custom-btn-info" title="Manage Workflow">
                                    <i class="fas fa-sitemap me-1"></i> Workflow
                                </a>
                                <a href="{{ route('superadmin.leave-types.edit', $leaveType->id) }}" class="custom-btn-sm custom-btn-warning" title="Edit">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <form action="{{ route('superadmin.leave-types.destroy', $leaveType->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this leave type? This action cannot be undone, especially if workflows or leave requests are associated with it.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="custom-btn-sm custom-btn-danger" title="Delete">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    @if(!$leaveTypes->isEmpty() && $leaveTypes->hasPages())
        <div class="mt-4 d-flex justify-content-center pagination-wrapper"> {{-- Custom pagination wrapper --}}
            {{ $leaveTypes->links() }}
        </div>
    @endif
</div>
@endsection

{{-- No @section('css') needed because styles are assumed to be in student.css (loaded by layouts.app) --}}