{{-- File: resources/views/superadmin/leave_types/edit.blade.php --}}
@extends('layouts.app') {{-- This layout includes the superadmin sidebar for admin role --}}

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8"> {{-- Or col-md-10 col-lg-8 --}}

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Edit Leave Type: <span class="text-muted">{{ $leaveType->name }}</span></h1>
                <a href="{{ route('superadmin.leave-types.index') }}" class="btn btn-outline-secondary">
                    Back to List
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Leave Type Details</h6>
                </div>
                <form method="POST" action="{{ route('superadmin.leave-types.update', $leaveType->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Leave Type Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $leaveType->name) }}"
                                   required>
                            @error('name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description"
                                      name="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description', $leaveType->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                         <div class="mb-3 form-check">
                            <input type="checkbox"
                                   id="is_active"
                                   name="is_active"
                                   class="form-check-input"
                                   value="1" {{ old('is_active', $leaveType->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                            @error('is_active')
                                 <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                    <div class="card-footer text-end"> {{-- Align buttons to the right --}}
                        <a href="{{ route('superadmin.leave-types.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Leave Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection