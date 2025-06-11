<!-- File: resources/views/superadmin/edit-user.blade.php -->
@extends('layouts.app')

@section('title', 'Edit User - ' . $user->name)

{{-- MODIFICATION 1: Add this new CSS section to fix the button styles --}}
@section('css')
<style>
    /* 
     * THE FIX:
     * This rule targets ANY element with the class "btn" inside the card footer.
     * It forces both the <button> and the <a> link to have the same blue style,
     * overriding the conflicting rule from student.css.
    */
    .card .card-footer .btn {
        background-color: #3498db; /* The blue color from your student.css file */
        border-color: #3498db;
        color: #fff; /* White text for contrast */
        padding: 10px 15px; /* Consistent padding */
        text-decoration: none; /* Removes underline from the 'Cancel' link */
        border-radius: 4px;
        border: none;
    }

    .card .card-footer .btn:hover {
        background-color: #2980b9; /* Matching hover color */
        border-color: #2980b9;
    }
    
    /* This removes the extra top margin that student.css adds to buttons */
    .card-footer button.btn {
        margin-top: 0;
    }
</style>
@endsection


@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Edit User: <span style="font-weight: normal;">{{ $user->name }}</span></h1>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            {{-- The card-warning class is now overridden by our new CSS, but it's good to keep for consistency --}}
            <div class="card card-warning"> 
                <div class="card-header">
                    <h3 class="card-title">Update User Details 🧑</h3>
                </div>
                <form method="POST" action="{{ route('superadmin.users.update', $user->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <h5 class="alert-heading">Please correct the errors below:</h5>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Form fields... (no changes needed here) --}}
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <hr>
                        <p class="text-muted">Update Password (optional):</p>
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            <small class="form-text text-muted">Leave blank to keep the current password.</small>
                            @error('password')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                        </div>
                        <hr>

                        <div class="form-group">
                            <label for="role">Role <span class="text-danger">*</span></label>
                            <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                                <option value="">-- Select Role --</option>
                                @php $currentRole = old('role', $user->role); @endphp
                                <option value="student" {{ $currentRole == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="hod" {{ $currentRole == 'hod' ? 'selected' : '' }}>HOD</option>
                                <option value="dsa" {{ $currentRole == 'dsa' ? 'selected' : '' }}>DSA</option>
                                <option value="daa" {{ $currentRole == 'daa' ? 'selected' : '' }}>DAA</option>
                                <option value="president" {{ $currentRole == 'president' ? 'selected' : '' }}>President</option>
                                <option value="sso" {{ $currentRole == 'sso' ? 'selected' : '' }}>SSO</option>
                                <option value="admin" {{ $currentRole == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="superadmin" {{ $currentRole == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                            </select>
                            @error('role')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="department_id">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                                <option value="">-- Select Department --</option>
                                @php $currentDepartment = old('department_id', $user->department_id); @endphp
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ $currentDepartment == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div id="student-fields" style="{{ old('role', $user->role) == 'student' ? 'display: block;' : 'display: none;' }}">
                            <hr>
                            <p class="text-muted">Student Specific Information:</p>
                            <div class="form-group">
                                <label for="program">Program</label>
                                <input type="text" id="program" name="program" class="form-control @error('program') is-invalid @enderror" value="{{ old('program', $user->program) }}">
                                @error('program')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="class">Class</label>
                                <input type="text" id="class" name="class" class="form-control @error('class') is-invalid @enderror" value="{{ old('class', $user->class) }}">
                                @error('class')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        {{-- MODIFICATION 2: Removed the specific color classes. Our new CSS will handle the styling. --}}
                        <button type="submit" class="btn">Update User</button>
                        <a href="{{ route('superadmin.users.index') }}" class="btn ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
{{-- (No changes needed in the script section) --}}
@endpush