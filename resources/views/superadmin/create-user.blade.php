@extends('layouts.app-no-sidebar') {{-- Adjust to your actual layout file --}}

@section('title', 'Create New User')

{{-- 
    MODIFICATION 1: 
    THIS IS THE REAL FIX. We are changing @section('css') to @push('styles')
    so that the styles are correctly injected into your layout file.
--}}
@push('styles')
<style>
    /* 
     * This CSS block will now be correctly loaded.
     * It targets ANY element with the class "btn" inside the card footer
     * and forces them to have the same blue style.
    */
    .card .card-footer .btn {
        background-color: #3498db !important; /* Blue color */
        border-color: #3498db !important;
        color: #fff !important;               /* White text */
        padding: 10px 15px !important;        /* Consistent padding */
        text-decoration: none !important;     /* Removes underline from links */
        border-radius: 4px !important;
        border: none !important;
    }

    .card .card-footer .btn:hover {
        background-color: #2980b9 !important; /* Darker blue on hover */
        border-color: #2980b9 !important;
        color: #fff !important;
    }
    
    /* This removes the extra top margin that student.css adds to buttons */
    .card-footer button.btn {
        margin-top: 0 !important;
    }
</style>
@endpush


@section('content_header')
    <div class="container-fluid pt-4">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Create New User 👤</h1>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title">User Details</h3></div>
                <form method="POST" action="{{ route('superadmin.users.store') }}">
                    @csrf
                    <div class="card-body">
                        {{-- Name, Email, Password, Confirm Password, Role --}}
                        {{-- (These fields remain the same as your last version) --}}
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
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
                            @error('role') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>

                        {{-- Department Dropdown --}}
                        <div class="form-group">
                            <label for="department_id">Department <span id="department_required_star" class="text-danger" style="display:none;">*</span></label>
                            <select name="department_id" id="department_id" class="form-control @error('department_id') is-invalid @enderror">
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                        </div>

                        {{-- Student Specific Fields --}}
                        <div id="student-fields" style="{{ old('role') == 'student' ? 'display: block;' : 'display: none;' }}">
                            <hr>
                            <p class="text-muted">Student Specific Information:</p>

                            {{-- Program Dropdown (REPLACED TEXT INPUT) --}}
                            <div class="form-group">
                                <label for="program_id">Program <span class="text-danger">*</span></label>
                                <select name="program_id" id="program_id" class="form-control @error('program_id') is-invalid @enderror" disabled>
                                    <option value="">-- Select Department First --</option>
                                    {{-- Options will be populated by JavaScript --}}
                                </select>
                                @error('program_id') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>

                            {{-- Class Field (Still text input, or change to dependent dropdown later) --}}
                            <div class="form-group">
                                <label for="class">Class/Year <span class="text-danger">*</span></label>
                                <input type="text" name="class" id="class" class="form-control @error('class') is-invalid @enderror" value="{{ old('class') }}">
                                {{-- If 'class' becomes a dropdown:
                                <select name="class_id" id="class_id" class="form-control @error('class_id') is-invalid @enderror" disabled>
                                    <option value="">-- Select Program First --</option>
                                </select>
                                --}}
                                @error('class') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn">Create User</button>
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
    const roleSelect = document.getElementById('role');
    const departmentSelect = document.getElementById('department_id');
    const programSelect = document.getElementById('program_id'); // Changed ID
    const classSelect = document.getElementById('class'); // Assuming class is still text, or use class_id for select
    const studentFieldsDiv = document.getElementById('student-fields');
    const departmentRequiredStar = document.getElementById('department_required_star');

    // Store old values for re-population on validation error
    const oldDepartmentId = "{{ old('department_id') }}";
    const oldProgramId = "{{ old('program_id') }}"; // Use program_id
    // const oldClassValue = "{{ old('class') }}"; // If class remains text

    function toggleStudentFields() {
        const isStudent = roleSelect.value === 'student';
        const isHod = roleSelect.value === 'hod';
        studentFieldsDiv.style.display = isStudent ? 'block' : 'none';

        // Make department required for student and HOD
        if (isStudent || isHod) {
            departmentRequiredStar.style.display = 'inline';
            // departmentSelect.setAttribute('required', 'required'); // Validation handled by FormRequest
        } else {
            departmentRequiredStar.style.display = 'none';
            // departmentSelect.removeAttribute('required');
        }

        if (isStudent) {
            // programSelect.setAttribute('required', 'required');
            // classSelect.setAttribute('required', 'required'); // If class is text input
            // Or class_idSelect.setAttribute('required', 'required'); if class is select
            if (departmentSelect.value) {
                fetchPrograms(departmentSelect.value, oldProgramId); // Pass old program_id for pre-selection
            } else {
                clearProgramSelect();
                clearClassSelect(); // Or class_id select
            }
        } else {
            clearProgramSelect(true); // Disable if not student
            clearClassSelect(true);   // Disable if not student
            // programSelect.removeAttribute('required');
            // classSelect.removeAttribute('required');
        }
    }

    function clearProgramSelect(disabled = false) {
        programSelect.innerHTML = '<option value="">-- Select Department First --</option>';
        programSelect.disabled = disabled;
    }
    function clearClassSelect(disabled = false) { // Assuming class is text or you have a class_id select
        // If class is text: classSelect.value = ''; classSelect.disabled = disabled;
        // If class is select: classSelect.innerHTML = '<option value="">-- Select Program First --</option>'; classSelect.disabled = disabled;
        if (classSelect.tagName === 'SELECT') {
            classSelect.innerHTML = '<option value="">-- Select Program First --</option>';
        } else {
            classSelect.value = '';
        }
        classSelect.disabled = disabled;
    }

    function fetchPrograms(departmentId, programIdToSelect = null) {
        if (!departmentId) {
            clearProgramSelect(roleSelect.value !== 'student');
            clearClassSelect(roleSelect.value !== 'student');
            return;
        }
        programSelect.innerHTML = '<option value="">-- Loading Programs... --</option>';
        programSelect.disabled = true;
        clearClassSelect(roleSelect.value !== 'student');

        // IMPORTANT: Ensure this route name matches your web.php inside superadmin.api group
        const url = `{{ route('superadmin.api.departments.programs', ['department' => ':departmentId']) }}`.replace(':departmentId', departmentId);

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok for programs.');
                return response.json();
            })
            .then(programs => {
                programSelect.innerHTML = '<option value="">-- Select Program --</option>';
                if (programs.length > 0) {
                    programs.forEach(program => {
                        const option = document.createElement('option');
                        option.value = program.id; // Use program ID as value
                        option.textContent = `${program.name} (${program.code})`;
                        if (programIdToSelect && program.id == programIdToSelect) {
                            option.selected = true;
                        }
                        programSelect.appendChild(option);
                    });
                    programSelect.disabled = false;
                    // If a program was pre-selected, trigger change to potentially load classes
                    if (programIdToSelect && programSelect.value == programIdToSelect) {
                        // programSelect.dispatchEvent(new Event('change')); // If classes depend on program
                    }
                } else {
                    programSelect.innerHTML = '<option value="">-- No Programs Available --</option>';
                    programSelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error fetching programs:', error);
                programSelect.innerHTML = '<option value="">-- Error Loading --</option>';
                programSelect.disabled = true;
            });
    }

    // Placeholder for fetchClasses if you make 'class' a dependent dropdown later
    // function fetchClasses(programId, classIdToSelect = null) { ... }

    roleSelect.addEventListener('change', toggleStudentFields);
    departmentSelect.addEventListener('change', function() {
        if (roleSelect.value === 'student') {
            fetchPrograms(this.value, (this.value === oldDepartmentId ? oldProgramId : null) ); // Pass oldProgramId only if dept matches oldDept
        } else {
            clearProgramSelect(true);
            clearClassSelect(true);
        }
    });

    // programSelect.addEventListener('change', function() {
    //     if (roleSelect.value === 'student') {
    //         fetchClasses(this.value, (this.value === oldProgramId ? oldClassId : null) ); // If class becomes dependent
    //     } else {
    //         clearClassSelect(true);
    //     }
    // });

    // Initial setup on page load
    toggleStudentFields(); // This will call fetchPrograms if role is student and department is pre-selected by old()
});
</script>
@endpush