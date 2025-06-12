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
            <div class="card card-warning">
                <div class="card-header"><h3 class="card-title">Update User Details 🧑</h3></div>
                <form method="POST" action="{{ route('superadmin.users.update', $user->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        {{-- Name, Email, Password, Role --}}
                        {{-- (These fields remain the same) --}}
                         <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                        <hr><p class="text-muted">Update Password (optional):</p>
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            <small class="form-text text-muted">Leave blank to keep current password.</small>
                            @error('password') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
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
                             @error('role') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>

                        {{-- Department Dropdown --}}
                        <div class="form-group">
                            <label for="department_id">Department <span id="department_required_star_edit" class="text-danger" style="display:none;">*</span></label>
                            <select name="department_id" id="department_id_edit" class="form-control @error('department_id') is-invalid @enderror">
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>

                        {{-- Student Specific Fields --}}
                        <div id="student-fields-edit" style="{{ old('role', $user->role) == 'student' ? 'display: block;' : 'display: none;' }}">
                            <hr>
                            <p class="text-muted">Student Specific Information:</p>

                            {{-- Program Dropdown --}}
                            <div class="form-group">
                                <label for="program_id_edit">Program <span class="text-danger">*</span></label>
                                <select name="program_id" id="program_id_edit" class="form-control @error('program_id') is-invalid @enderror" disabled>
                                    <option value="">-- Select Department First --</option>
                                    {{-- Options populated by JS. Pre-selection handled by JS. --}}
                                </select>
                                @error('program_id') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>

                            {{-- Class Field --}}
                            <div class="form-group">
                                <label for="class_edit">Class/Year <span class="text-danger">*</span></label>
                                <input type="text" name="class" id="class_edit" class="form-control @error('class') is-invalid @enderror" value="{{ old('class', $user->class) }}">
                                @error('class') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Script for edit form - ensure unique IDs for elements
    const roleSelectEdit = document.getElementById('role'); // Assuming ID is 'role' as in create form
    const departmentSelectEdit = document.getElementById('department_id_edit');
    const programSelectEdit = document.getElementById('program_id_edit');
    const classSelectEdit = document.getElementById('class_edit');
    const studentFieldsDivEdit = document.getElementById('student-fields-edit');
    const departmentRequiredStarEdit = document.getElementById('department_required_star_edit');

    const initialUserRole = "{{ old('role', $user->role) }}";
    const initialDepartmentId = "{{ old('department_id', $user->department_id) }}";
    const initialProgramId = "{{ old('program_id', $user->program_id) }}"; // Use program_id
    // const initialClassValue = "{{ old('class', $user->class) }}";

    function toggleStudentFieldsEdit() {
        const isStudent = roleSelectEdit.value === 'student';
        const isHod = roleSelectEdit.value === 'hod';
        studentFieldsDivEdit.style.display = isStudent ? 'block' : 'none';

        if (isStudent || isHod) {
            departmentRequiredStarEdit.style.display = 'inline';
        } else {
            departmentRequiredStarEdit.style.display = 'none';
        }

        if (isStudent) {
            if (departmentSelectEdit.value) { // If a department is selected
                fetchProgramsEdit(departmentSelectEdit.value, initialProgramId);
            } else { // No department selected, clear program and class
                clearProgramSelectEdit();
                clearClassSelectEdit();
            }
        } else { // Not a student
            clearProgramSelectEdit(true); // Disable program dropdown
            clearClassSelectEdit(true);   // Disable class field/dropdown
        }
    }

    function clearProgramSelectEdit(disabled = false) {
        programSelectEdit.innerHTML = '<option value="">-- Select Department First --</option>';
        programSelectEdit.disabled = disabled;
    }
    function clearClassSelectEdit(disabled = false) {
        if (classSelectEdit.tagName === 'SELECT') {
            classSelectEdit.innerHTML = '<option value="">-- Select Program First --</option>';
        } else {
            classSelectEdit.value = '';
        }
        classSelectEdit.disabled = disabled;
    }


    function fetchProgramsEdit(departmentId, programIdToSelect = null) {
        if (!departmentId) {
            clearProgramSelectEdit(roleSelectEdit.value !== 'student');
            clearClassSelectEdit(roleSelectEdit.value !== 'student');
            return;
        }
        programSelectEdit.innerHTML = '<option value="">-- Loading Programs... --</option>';
        programSelectEdit.disabled = true;
        clearClassSelectEdit(roleSelectEdit.value !== 'student');

        const url = `{{ route('superadmin.api.departments.programs', ['department' => ':departmentId']) }}`.replace(':departmentId', departmentId);
        fetch(url)
            .then(response => response.json())
            .then(programs => {
                programSelectEdit.innerHTML = '<option value="">-- Select Program --</option>';
                if (programs.length > 0) {
                    programs.forEach(program => {
                        const option = document.createElement('option');
                        option.value = program.id; // Use program ID
                        option.textContent = `${program.name} (${program.code})`;
                        if (programIdToSelect && program.id == programIdToSelect) {
                            option.selected = true;
                        }
                        programSelectEdit.appendChild(option);
                    });
                    programSelectEdit.disabled = false;
                    // If a program was pre-selected, trigger change if classes depend on it
                    if (programIdToSelect && programSelectEdit.value == programIdToSelect) {
                        // programSelectEdit.dispatchEvent(new Event('change'));
                    }
                } else {
                    programSelectEdit.innerHTML = '<option value="">-- No Programs Available --</option>';
                    programSelectEdit.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error fetching programs for edit:', error);
                programSelectEdit.innerHTML = '<option value="">-- Error Loading --</option>';
                programSelectEdit.disabled = true;
            });
    }

    // Event Listeners
    roleSelectEdit.addEventListener('change', toggleStudentFieldsEdit);
    departmentSelectEdit.addEventListener('change', function() {
        if (roleSelectEdit.value === 'student') {
            // When department changes, only pre-select old program if it's the initial department
            const programToSelect = (this.value === initialDepartmentId) ? initialProgramId : null;
            fetchProgramsEdit(this.value, programToSelect);
        } else {
            clearProgramSelectEdit(true);
            clearClassSelectEdit(true);
        }
    });

    // programSelectEdit.addEventListener('change', function() { /* For class dropdown */ });

    // Initial population for edit form
    if (initialUserRole === 'student' && initialDepartmentId) {
        fetchProgramsEdit(initialDepartmentId, initialProgramId);
    }
    toggleStudentFieldsEdit(); // Call once on load to set initial visibility and requirements
});
</script>
@endpush