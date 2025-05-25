@extends('layouts.app-no-sidebar') {{-- Adjust to your actual layout file --}}

@section('title', 'Create New User')

@section('content_header')
    <div class="container-fluid pt-4">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Create New User</h1>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">User Details</h3>
                </div>
                <form method="POST" action="{{ route('superadmin.users.store') }}">
                    @csrf
                    <div class="card-body">
                        {{-- Name Field --}}
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        {{-- Email Field --}}
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        {{-- Password Field --}}
                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        {{-- Confirm Password Field --}}
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                        </div>

                        {{-- Role Dropdown --}}
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
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        {{-- Department Dropdown --}}
                        <div class="form-group">
                            <label for="department_id">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $department) {{-- $departments is passed from SuperAdminController@create --}}
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        {{-- Student Specific Fields --}}
                        <div id="student-fields" style="{{ old('role') == 'student' ? 'display: block;' : 'display: none;' }}">
                            <hr>
                            <p class="text-muted">Student Specific Information:</p>

                            {{-- Program Dropdown --}}
                            <div class="form-group">
                                <label for="program">Program <span class="text-danger">*</span></label>
                                <select name="program" id="program" class="form-control @error('program') is-invalid @enderror">
                                    <option value="">-- Select Program --</option>
                                    {{-- Options will be populated by JavaScript --}}
                                </select>
                                @error('program')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            {{-- Class Dropdown --}}
                            <div class="form-group">
                                <label for="class">Class <span class="text-danger">*</span></label>
                                <select name="class" id="class" class="form-control @error('class') is-invalid @enderror">
                                    <option value="">-- Select Class --</option>
                                    {{-- Options will be populated by JavaScript --}}
                                </select>
                                @error('class')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
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
document.addEventListener('DOMContentLoaded', function () {
    console.log('SCRIPT START: DOMContentLoaded fired.');

    const roleSelect = document.getElementById('role');
    const departmentSelect = document.getElementById('department_id');
    const programSelect = document.getElementById('program');
    const classSelect = document.getElementById('class');
    const studentFieldsDiv = document.getElementById('student-fields');

    const oldProgramCode = "{{ old('program') }}"; // This will be the program CODE
    const oldClassCode = "{{ old('class') }}";     // This will be the class CODE
    const oldDepartmentId = "{{ old('department_id') }}"; // Get old department ID

    console.log('Elements:', { roleSelect, departmentSelect, programSelect, classSelect, studentFieldsDiv });
    console.log('Old values:', { oldDepartmentId, oldProgramCode, oldClassCode });

    if (!roleSelect || !departmentSelect || !programSelect || !classSelect || !studentFieldsDiv) {
        console.error('CRITICAL: One or more essential select/div elements not found!');
        return;
    }

    function clearSelect(selectElement, defaultOptionText = '-- Select --', disabled = true) {
        selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
        selectElement.disabled = disabled;
    }

    function populateSelect(selectElement, data, valueField = 'code', textField = 'name', selectedValue = null) {
        const originalDefaultText = `-- Select ${selectElement.id.charAt(0).toUpperCase() + selectElement.id.slice(1)} --`;
        clearSelect(selectElement, originalDefaultText, false); // Enable before populating

        let valueFoundAndSelected = false;
        if (data && data.length > 0) {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item[valueField];
                option.textContent = item[textField];
                if (selectedValue && item[valueField] == selectedValue) {
                    option.selected = true;
                    valueFoundAndSelected = true;
                }
                selectElement.appendChild(option);
            });
        }

        if (!(data && data.length > 0) || (selectedValue && !valueFoundAndSelected)) {
             // If no data, or if a selection was attempted but not found, ensure a clear state
            if (!(data && data.length > 0)) {
                selectElement.innerHTML = `<option value="" disabled>-- No Options Available --</option>`;
            }
            selectElement.disabled = true;
            if (data && data.length > 0 && selectedValue && !valueFoundAndSelected) { // Add a placeholder if selected value wasn't found in populated options
                 const firstOption = selectElement.firstChild;
                 const tempOption = document.createElement('option');
                 tempOption.value = "";
                 tempOption.textContent = `-- Select ${selectElement.id.charAt(0).toUpperCase() + selectElement.id.slice(1)} --`;
                 tempOption.selected = true;
                 tempOption.disabled = true; // Make it unselectable
                 selectElement.insertBefore(tempOption, firstOption);
                 selectElement.value = ""; // Reset to the placeholder
                 selectElement.disabled = false;
            }
        } else {
            selectElement.disabled = false;
        }


        // Trigger change if a value was pre-selected AND successfully selected
        if (valueFoundAndSelected) {
            console.log(`Triggering change for ${selectElement.id} due to pre-selection of ${selectedValue}`);
            // Dispatching event needs to be done carefully to avoid race conditions if the browser isn't ready
            // setTimeout(() => selectElement.dispatchEvent(new Event('change', { bubbles: true })), 0);
             selectElement.dispatchEvent(new Event('change', { bubbles: true }));
        } else if (selectedValue) {
            console.warn(`Tried to pre-select ${selectedValue} for ${selectElement.id}, but it was not found. Current value: ${selectElement.value}`);
        }
    }

    function fetchPrograms(departmentId, programCodeToSelect = null) {
        console.log(`FETCH PROGRAMS: Called with departmentId = ${departmentId}, programCodeToSelect = ${programCodeToSelect}`);
        clearSelect(programSelect, '-- Loading Programs --');
        clearSelect(classSelect, '-- Select Program First --');

        if (!departmentId) {
            clearSelect(programSelect, '-- Select Department First --', false); // Keep enabled
            return;
        }

        const url = `{{ url('superadmin/api/departments') }}/${departmentId}/programs`;
        console.log(`FETCH PROGRAMS: Fetching from URL: ${url}`);

        fetch(url)
            .then(response => {
                console.log('FETCH PROGRAMS: Received response object:', response);
                if (!response.ok) {
                    console.error(`FETCH PROGRAMS: Network response not OK. Status: ${response.status} ${response.statusText}`);
                    clearSelect(programSelect, '-- Error Loading Programs --', false); // Keep enabled
                    throw new Error(`Network response for programs was not ok (${response.status})`);
                }
                return response.json();
            })
            .then(data => {
                console.log('FETCH PROGRAMS: Data received for programs:', data);
                populateSelect(programSelect, data, 'code', 'name', programCodeToSelect);
            })
            .catch(error => {
                console.error('FETCH PROGRAMS: Catch block error:', error);
                clearSelect(programSelect, '-- Error Loading Programs --', false); // Keep enabled
            });
    }

    function fetchClasses(programCode, classCodeToSelect = null) {
        console.log(`FETCH CLASSES: Called with programCode = ${programCode}, classCodeToSelect = ${classCodeToSelect}`);
        clearSelect(classSelect, '-- Loading Classes --');

        if (!programCode) {
            clearSelect(classSelect, '-- Select Program First --', false); // Keep enabled
            return;
        }

        const url = `{{ url('superadmin/api/programs') }}/${programCode}/classes`;
        console.log(`FETCH CLASSES: Fetching from URL: ${url}`);

        fetch(url)
            .then(response => {
                console.log('FETCH CLASSES: Received response object:', response);
                if (!response.ok) {
                    console.error(`FETCH CLASSES: Network response not OK. Status: ${response.status} ${response.statusText}`);
                    clearSelect(classSelect, '-- Error Loading Classes --', false); // Keep enabled
                    throw new Error(`Network response for classes was not ok (${response.status})`);
                }
                return response.json();
            })
            .then(data => {
                console.log('FETCH CLASSES: Data received for classes:', data);
                populateSelect(classSelect, data, 'code', 'name', classCodeToSelect);
            })
            .catch(error => {
                console.error('FETCH CLASSES: Catch block error:', error);
                clearSelect(classSelect, '-- Error Loading Classes --', false); // Keep enabled
            });
    }

    function handleRoleChange() {
        console.log('HANDLE ROLE CHANGE: Role changed to', roleSelect.value);
        const isStudent = roleSelect.value === 'student';
        studentFieldsDiv.style.display = isStudent ? 'block' : 'none';

        departmentSelect.required = isStudent;
        programSelect.required = isStudent;
        classSelect.required = isStudent;

        if (isStudent) {
            if (departmentSelect.value) { // If department is already selected (e.g. old input or user action)
                console.log('HANDLE ROLE CHANGE: Role is student and department has value. Fetching programs.');
                fetchPrograms(departmentSelect.value, oldProgramCode);
            } else {
                console.log('HANDLE ROLE CHANGE: Role is student but no department selected. Clearing dropdowns.');
                clearSelect(programSelect, '-- Select Department First --');
                clearSelect(classSelect, '-- Select Program First --');
            }
        } else {
            clearSelect(programSelect);
            clearSelect(classSelect);
            // departmentSelect.removeAttribute('required'); // Decide if department is always required or only for students
            programSelect.removeAttribute('required');
            classSelect.removeAttribute('required');
        }
    }

    function handleDepartmentChange() {
        console.log('HANDLE DEPARTMENT CHANGE: Department changed to', departmentSelect.value);
        if (roleSelect.value === 'student') {
            // When department changes, we always want to fetch programs.
            // If this change is due to old('department_id') being set, oldProgramCode will be used.
            // If it's a manual change, oldProgramCode will likely be empty or irrelevant for the NEW department.
            // So we pass oldProgramCode here. If the new department doesn't have this program, it won't be selected.
            fetchPrograms(this.value, oldProgramCode);
        } else {
            clearSelect(programSelect);
            clearSelect(classSelect);
        }
    }

    programSelect.addEventListener('change', function() {
        console.log('PROGRAM SELECT CHANGE: Program changed to', this.value);
        if (roleSelect.value === 'student') {
            // When program is changed by user, we pass the oldClassCode only if the selected program
            // matches the oldProgramCode. Otherwise, we are selecting a NEW program, so no old class applies.
            const classToSelect = (this.value === oldProgramCode) ? oldClassCode : null;
            fetchClasses(this.value, classToSelect);
        } else {
            clearSelect(classSelect);
        }
    });

    roleSelect.addEventListener('change', handleRoleChange);
    departmentSelect.addEventListener('change', handleDepartmentChange);

    // Initial setup on page load
    console.log('SCRIPT END: Initializing fields on page load.');
    // Trigger role change handler first. This will show/hide student fields.
    // If student fields become visible, it will then check department and potentially fetch programs.
    handleRoleChange();

    // If department_id was set by old() and role is student, ensure programs are fetched for it.
    // The handleRoleChange might already do this if departmentSelect.value is populated by `old()`.
    // This is a bit of a safeguard for initial load with old data.
    if (roleSelect.value === 'student' && oldDepartmentId && departmentSelect.value === oldDepartmentId) {
        // If fetchPrograms was already called by handleRoleChange with oldProgramCode, this might be redundant
        // or could cause a quick double fetch. The logic within fetchPrograms and populateSelect
        // aims to handle pre-selection correctly.
        // Let's ensure program dropdown has a chance to get populated if it wasn't already.
        if (programSelect.options.length <= 1 && departmentSelect.value) { // If only default option exists
            console.log('Initial Load: Role is student, oldDepartmentId exists. Re-checking programs fetch.');
            fetchPrograms(departmentSelect.value, oldProgramCode);
        }
    }
});
</script>
@endpush