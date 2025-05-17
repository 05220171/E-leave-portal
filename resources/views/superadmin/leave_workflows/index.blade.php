{{-- File: resources/views/superadmin/leave_workflows/index.blade.php --}}
@extends('layouts.app') {{-- Assuming this includes the superadmin sidebar --}}

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Workflow for: <span class="text-primary">{{ $leaveType->name }}</span></h1>
        <a href="{{ route('superadmin.leave-types.index') }}" class="btn btn-outline-secondary">
            Back to Leave Types
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

    {{-- Section to Add New Workflow Step --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Add New Workflow Step</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('superadmin.leave-types.workflows.store', $leaveType->id) }}">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-5 mb-3">
                        <label for="approver_role" class="form-label">Approver Role <span class="text-danger">*</span></label>
                        <select name="approver_role" id="approver_role" class="form-select @error('approver_role') is-invalid @enderror" required>
                            <option value="">-- Select Role --</option>
                            @foreach($assignableRoles as $role)
                                <option value="{{ $role }}" {{ old('approver_role') == $role ? 'selected' : '' }}>
                                    {{ Str::title(str_replace('_', ' ', $role)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('approver_role')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="action_type" class="form-label">Action Type <span class="text-danger">*</span></label>
                        <select name="action_type" id="action_type" class="form-select @error('action_type') is-invalid @enderror" required>
                             <option value="">-- Select Action --</option>
                            @foreach($actionTypes as $action)
                                <option value="{{ $action }}" {{ old('action_type') == $action ? 'selected' : '' }}>
                                    {{ Str::title(str_replace('_', ' ', $action)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('action_type')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" class="btn btn-success w-100">Add Step</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Section to Display Existing Workflow Steps --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Current Workflow Steps (Order of Approval)</h6>
        </div>
        <div class="card-body">
            @if($workflows->isEmpty())
                <p class="text-center text-muted">No workflow steps defined yet for this leave type.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th style="width: 10%;">Step #</th>
                                <th>Approver Role</th>
                                <th>Action Type</th>
                                <th style="width: 15%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workflows as $workflow)
                                <tr>
                                    <td>{{ $workflow->step_number }}</td>
                                    <td>{{ Str::title(str_replace('_', ' ', $workflow->approver_role)) }}</td>
                                    <td>{{ Str::title(str_replace('_', ' ', $workflow->action_type)) }}</td>
                                    <td>
                                        {{-- Edit button can be added later if edit functionality is implemented --}}
                                        {{-- <a href="{{ route('superadmin.leave-types.workflows.edit', [$leaveType->id, $workflow->id]) }}" class="btn btn-sm btn-warning me-1">Edit</a> --}}
                                        <form action="{{ route('superadmin.leave-types.workflows.destroy', [$leaveType->id, $workflow->id]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this workflow step?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection