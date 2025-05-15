@extends('layouts.app-no-sidebar') {{-- <<< THIS IS THE CRUCIAL CHANGE --}}

@section('title', 'Create New User') {{-- Optional: if your app-no-sidebar layout yields a title --}}

@section('content_header')
    <div class="container-fluid pt-4"> {{-- Adjusted padding since no sidebar might change spacing --}}
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Create New User</h1>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container"> {{-- You might want this container to be container-fluid if it's a full-page form without a sidebar --}}
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">User Details</h3>
                </div>
                <form method="POST" action="{{ route('superadmin.users.store') }}">
                    @csrf
                    <div class="card-body">
                        {{-- ... (rest of your form fields are fine) ... --}}

                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="role">Role <span class="text-danger">*</span></label>
                            <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                                <option value="" {{ old('role') == "" ? 'selected' : '' }}>-- Select Role --</option>
                                <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="hod" {{ old('role') == 'hod' ? 'selected' : '' }}>HOD</option>
                                <option value="dsa" {{ old('role') == 'dsa' ? 'selected' : '' }}>DSA</option>
                                <option value="sso" {{ old('role') == 'sso' ? 'selected' : '' }}>SSO</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="superadmin" {{ old('role') == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                            </select>
                            @error('role')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="department_id">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                                <option value="" {{ old('department_id') == "" ? 'selected' : '' }}>-- Select Department --</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div id="student-fields" style="{{ old('role') == 'student' ? 'display: block;' : 'display: none;' }}">
                            <hr>
                            <p class="text-muted">Student Specific Information:</p>
                            <div class="form-group">
                                <label for="program">Program</label>
                                <input type="text" id="program" name="program" class="form-control @error('program') is-invalid @enderror" value="{{ old('program') }}">
                                @error('program')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="class">Class</label>
                                <input type="text" id="class" name="class" class="form-control @error('class') is-invalid @enderror" value="{{ old('class') }}">
                                @error('class')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Create User</button>
                        <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
    // Your JavaScript to toggle student fields remains the same and is good.
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('role');
        const studentFieldsDiv = document.getElementById('student-fields');

        function toggleStudentFields() {
            if (roleSelect && studentFieldsDiv) {
                if (roleSelect.value === 'student') {
                    studentFieldsDiv.style.display = 'block';
                } else {
                    studentFieldsDiv.style.display = 'none';
                }
            }
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', toggleStudentFields);
            toggleStudentFields(); 
        }
    });
</script>
@endpush