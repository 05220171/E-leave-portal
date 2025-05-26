@extends('layouts.app') {{-- This layout loads student.css --}}

@section('title', 'Manage Workflow - ' . $leaveType->name)

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-section-title text-start mb-0">Manage Workflow for: <span class="fw-normal">{{ $leaveType->name }}</span></h1>
        <a href="{{ route('superadmin.leave-types.index') }}" class="custom-btn-sm custom-btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Leave Types
        </a>
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
    @if ($errors->any()) {{-- For form validation errors --}}
        <div class="custom-alert custom-alert-danger">
            <h6 class="fw-bold">Please correct the errors below:</h6>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- Section to Add New Workflow Step --}}
    <div class="card shadow-sm mb-4"> {{-- Using Bootstrap card, can be styled further by your global .card if needed --}}
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Add New Workflow Step</h5>
        </div>
        <form method="POST" action="{{ route('superadmin.leave-types.workflows.store', $leaveType->id) }}" class="needs-validation" novalidate>
            @csrf
            <div class="card-body" style="padding: 1.5rem;">
                <div class="row align-items-end">
                    <div class="col-md-5 mb-3">
                        <label for="approver_role" class="form-label fw-semibold">Approver Role <span class="text-danger">*</span></label>
                        <select name="approver_role" id="approver_role" class="form-select @error('approver_role') is-invalid @enderror elegant-textarea" required>
                            <option value="">-- Select Role --</option>
                            @foreach($assignableRoles as $role)
                                <option value="{{ $role }}" {{ old('approver_role') == $role ? 'selected' : '' }}>
                                    {{ Str::title(str_replace('_', ' ', $role)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('approver_role')
                            <div class="invalid-feedback"><strong>{{ $message }}</strong></div>
                        @enderror
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="action_type" class="form-label fw-semibold">Action Type <span class="text-danger">*</span></label>
                        <select name="action_type" id="action_type" class="form-select @error('action_type') is-invalid @enderror elegant-textarea" required>
                             <option value="">-- Select Action --</option>
                            @foreach($actionTypes as $action)
                                <option value="{{ $action }}" {{ old('action_type') == $action ? 'selected' : '' }}>
                                    {{ Str::title(str_replace('_', ' ', $action)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('action_type')
                            <div class="invalid-feedback"><strong>{{ $message }}</strong></div>
                        @enderror
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" class="custom-btn custom-btn-success w-100"><i class="fas fa-plus me-1"></i> Add Step</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Section to Display Existing Workflow Steps --}}
    <h3 class="mb-3 mt-4" style="font-weight: 600; color: #2980b9;">
        <i class="fas fa-sitemap me-2"></i> Current Workflow Steps <small class="text-muted">(Order of Approval)</small>
    </h3>
    <div class="custom-table-wrapper">
        <table class="custom-data-table">
            <thead>
                <tr>
                    <th style="width: 10%;">Step</th>
                    <th>Approver Role</th>
                    <th>Action Type</th>
                    <th style="width: 15%; text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($workflows->isEmpty())
                    <tr>
                        <td colspan="4" class="text-center">
                            <div class="py-3">No workflow steps defined yet for this leave type. Add a step using the form above.</div>
                        </td>
                    </tr>
                @else
                    @foreach($workflows as $workflow)
                        <tr>
                            <td>{{ $workflow->step_number }}</td>
                            <td>{{ Str::title(str_replace('_', ' ', $workflow->approver_role)) }}</td>
                            <td>
                                @if(strtolower($workflow->action_type) === 'approval')
                                    <span class="status-badge status-pending">{{ Str::title(str_replace('_', ' ', $workflow->action_type)) }}</span>
                                @else
                                    <span class="status-badge status-default">{{ Str::title(str_replace('_', ' ', $workflow->action_type)) }}</span>
                                @endif
                            </td>
                            <td class="actions-cell text-center">
                                {{-- Edit button placeholder
                                <a href="#" class="custom-btn-sm custom-btn-warning me-1" title="Edit Step (Future Feature)">
                                    <i class="fas fa-edit"></i>
                                </a>
                                --}}
                                <form action="{{ route('superadmin.leave-types.workflows.destroy', [$leaveType->id, $workflow->id]) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this workflow step? This will re-sequence subsequent steps.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="custom-btn-sm custom-btn-danger" title="Delete Step">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

{{-- No @section('css') needed as styles are global --}}

@push('scripts')
{{-- Standard Bootstrap 5 Form Validation Script (if not globally included in layouts.app.blade.php) --}}
<script>
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
@endpush