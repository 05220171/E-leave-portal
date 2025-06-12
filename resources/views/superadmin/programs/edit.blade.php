@extends('layouts.app') {{-- Or your superadmin layout --}}

@section('title', 'Edit Program: ' . $program->name)

{{-- If your custom styles are not global, copy the @section('css') here --}}

@section('content')
<div class="container mt-4">
     <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="page-section-title text-start mb-0">Edit Program: <span class="fw-normal">{{ $program->name }}</span></h1>
                <a href="{{ route('superadmin.programs.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Update Program Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('superadmin.programs.update', $program->code) }}"> {{-- Use program code for route --}}
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Program Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $program->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="code" class="form-label">Program Code <span class="text-danger">*</span></label>
                            <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $program->code) }}" required>
                             <small class="form-text text-muted">Short unique code for the program.</small>
                            @error('code')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $program->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary header-action-btn"> {{-- Use your custom button class --}}
                                <i class="fas fa-save me-1"></i> Update Program
                            </button>
                            <a href="{{ route('superadmin.programs.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection