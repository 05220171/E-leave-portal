@extends('layouts.app') {{-- Your main application layout --}}

@section('content')
<div class="container mt-4"> {{-- Assuming Bootstrap for main content styling --}}
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Apply for Leave</h2>
                </div>
                <div class="card-body">
                    {{-- Display Session Success/Error Messages --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Display Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h5 class="alert-heading">Please correct the errors below:</h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('student.store-leave') }}" method="POST" enctype="multipart/form-data" class="leave-form">
                        @csrf

                        <div class="mb-3 form-group">
                            <label for="leave_type_id" class="form-label">Leave Type: <span class="text-danger">*</span></label>
                            <select name="leave_type_id" id="leave_type_id" required class="form-select @error('leave_type_id') is-invalid @enderror">
                                <option value="">-- Select Leave Type --</option>
                                @if(isset($activeLeaveTypes) && $activeLeaveTypes->count() > 0)
                                    @foreach($activeLeaveTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No leave types available. Please contact admin.</option>
                                @endif
                            </select>
                            @error('leave_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3 form-group">
                                    <label for="from_date" class="form-label">Start Date: <span class="text-danger">*</span></label>
                                    <input type="date" name="from_date" id="from_date" value="{{ old('from_date') }}" required class="form-control @error('from_date') is-invalid @enderror">
                                    @error('from_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 form-group">
                                    <label for="to_date" class="form-label">End Date: <span class="text-danger">*</span></label>
                                    <input type="date" name="to_date" id="to_date" value="{{ old('to_date') }}" required class="form-control @error('to_date') is-invalid @enderror">
                                    @error('to_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="leave_days" class="form-label">Number of Working Days:</label>
                            <input type="text" id="leave_days" name="leave_days" readonly class="form-control bg-light"> {{-- bg-light for readonly appearance --}}
                        </div>

                        <div class="mb-3 form-group">
                            <label for="reason" class="form-label">Reason: <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reason" rows="4" required class="form-control @error('reason') is-invalid @enderror">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-group">
                            <label for="document" class="form-label">Attach Document (Optional):</label>
                            <input type="file" name="document" id="document" class="form-control @error('document') is-invalid @enderror">
                            <small class="form-text text-muted">Allowed types: PDF, JPG, JPEG, PNG, DOC, DOCX. Max size: 2MB.</small>
                            @error('document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-actions mt-4 text-center"> {{-- Your styling class for actions --}}
                            <button type="submit" class="btn btn-primary px-4">Submit Leave Request</button>
                            {{-- Or use your custom classes: class="card-button button-apply" --}}
                        </div>
                    </form>
                </div> {{-- card-body --}}
            </div> {{-- card --}}
        </div> {{-- col --}}
    </div> {{-- row --}}
</div> {{-- container --}}

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fromDateInput = document.getElementById('from_date');
        const toDateInput = document.getElementById('to_date');
        const leaveDaysOutput = document.getElementById('leave_days');

        function calculateWorkingDays() {
            const startDateString = fromDateInput.value;
            const endDateString = toDateInput.value;

            if (startDateString && endDateString) {
                const start = new Date(startDateString);
                const end = new Date(endDateString);
                let workingDays = 0;

                if (end >= start) {
                    let currentDate = new Date(start.getTime()); // Use getTime() for reliable date copying for iteration
                    while (currentDate <= end) {
                        const dayOfWeek = currentDate.getDay(); // 0 = Sunday, 6 = Saturday
                        if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Exclude Sunday and Saturday
                            workingDays++;
                        }
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                    leaveDaysOutput.value = workingDays > 0 ? workingDays : ''; // Show empty if 0 for clarity, or just workingDays
                } else {
                    leaveDaysOutput.value = ''; // Or an error message, or 0
                }
            } else {
                leaveDaysOutput.value = ''; // Or 0
            }
        }

        if (fromDateInput && toDateInput && leaveDaysOutput) {
            fromDateInput.addEventListener('change', calculateWorkingDays);
            toDateInput.addEventListener('change', calculateWorkingDays);
            // Initial calculation if dates might be pre-filled (e.g., from old input on validation error)
            calculateWorkingDays();
        }
    });
</script>
@endsection