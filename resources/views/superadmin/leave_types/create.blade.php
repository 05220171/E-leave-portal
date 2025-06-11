@extends('layouts.app')

@section('title', 'Create New Leave Type - Super Admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-section-title text-start mb-0">Create New Leave Type</h1>
        <a href="{{ route('superadmin.leave-types.index') }}" class="custom-btn-sm custom-btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white"> {{-- Assuming you have custom styles for bg-primary text-white or are using Bootstrap --}}
             <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Leave Type Details</h5>
        </div>
        <form method="POST" action="{{ route('superadmin.leave-types.store') }}" class="needs-validation" novalidate>
            @csrf
            <div class="card-body" style="padding: 1.5rem;">
                @if ($errors->any())
                    <div class="custom-alert custom-alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Leave Type Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror elegant-textarea" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback"><strong>{{ $message }}</strong></div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror elegant-textarea" rows="3" placeholder="Provide a clear description...">{{ old('description') }}</textarea>
                    <small class="form-text text-muted">This may be shown to students and approvers.</small>
                    @error('description')
                        <div class="invalid-feedback"><strong>{{ $message }}</strong></div>
                    @enderror
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_active">
                        Active
                    </label>
                    <small class="form-text text-muted d-block">Inactive types won't be selectable by students.</small>
                    @error('is_active')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            {{-- MODIFIED CARD FOOTER --}}
            <div class="card-footer bg-light d-flex align-items-center">
                {{-- Cancel button, will have a margin to its right to separate from the Create button --}}
                <a href="{{ route('superadmin.leave-types.index') }}" class="custom-btn custom-btn-sm custom-btn-secondary me-2 form-footer-button">Cancel</a>

                {{-- Create button, pushed to the far right using ms-auto --}}
                <button type="submit" class="custom-btn custom-btn-sm custom-btn-primary ms-auto form-footer-button">
                    <i class="fas fa-save me-1"></i> Create Leave Type
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
{{-- Standard Bootstrap 5 Form Validation Script --}}
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
@endsection