@extends('layouts.app') {{-- Your main application layout --}}
@section('content')
<div class="container mt-4"> {{-- Assuming Bootstrap for main content styling --}}
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Apply for Leave✍️</h2>
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
                            <label for="leave_type_id" class="form-label">Leave Type: <span class="text-danger"></span></label>
                            <select name="leave_type_id" id="leave_type_id" required class="form-select @error('leave_type_id') is-invalid @enderror">
                                <option value="">-- Select Leave Type --</option>
                                @php
                                    // CORRECTED: Matches database value "Weekend leave"
                                    $weekendLeaveTypeName = 'Weekend leave';
                                @endphp
                                @if(isset($activeLeaveTypes) && $activeLeaveTypes->count() > 0)
                                    @foreach($activeLeaveTypes as $type)
                                        <option value="{{ $type->id }}"
                                                {{ old('leave_type_id') == $type->id ? 'selected' : '' }}
                                                data-leave-type-name="{{ $type->name }}"> {{-- Pass name for JS --}}
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

                        {{-- Note for Weekend Leave --}}
                        <div id="weekend_leave_note" class="alert alert-info" style="display: none;">
                            <strong>Note:</strong> Weekend leave should only be on Saturdays and Sundays.
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3 form-group">
                                    <label for="from_date" class="form-label">Start Date: <span class="text-danger"></span></label>
                                    <input type="date" name="from_date" id="from_date" value="{{ old('from_date') }}" required class="form-control @error('from_date') is-invalid @enderror">
                                    @error('from_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 form-group">
                                    <label for="to_date" class="form-label">End Date: <span class="text-danger"></span></label>
                                    <input type="date" name="to_date" id="to_date" value="{{ old('to_date') }}" required class="form-control @error('to_date') is-invalid @enderror">
                                    @error('to_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="leave_days" class="form-label">Number of Days:</label>
                            <input type="text" id="leave_days" name="leave_days" readonly class="form-control bg-light">
                        </div>

                        <div class="mb-3 form-group">
                            <label for="reason" class="form-label">Reason: <span class="text-danger"></span></label>
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

                        <div class="form-actions mt-4 text-center">
                            <button type="submit" class="btn btn-primary px-4">Submit Leave Request</button>
                        </div>
                    </form>
                </div> {{-- card-body --}}
            </div> {{-- card --}}
        </div> {{-- col --}}
    </div> {{-- row --}}
</div> {{-- container --}}

<script>
document.addEventListener('DOMContentLoaded', function () {
    const leaveTypeSelect = document.getElementById('leave_type_id');
    const fromDateInput = document.getElementById('from_date');
    const toDateInput = document.getElementById('to_date');
    const leaveDaysOutput = document.getElementById('leave_days');
    const weekendLeaveNote = document.getElementById('weekend_leave_note');

    // CORRECTED: This will now be "Weekend leave" due to the @php block change
    const WEEKEND_LEAVE_TYPE_NAME = "{{ $weekendLeaveTypeName }}";

    let isWeekendLeaveSelected = false;

    function isWeekend(date) {
        const day = date.getDay();
        return day === 0 || day === 6; // 0 = Sunday, 6 = Saturday
    }

    function validateDateInputForWeekend(dateInput) {
        if (!isWeekendLeaveSelected || !dateInput.value) return true;

        const selectedDate = new Date(dateInput.value + 'T00:00:00');
        if (!isWeekend(selectedDate)) {
            alert('For Weekend Leave, please select only Saturdays or Sundays.');
            dateInput.value = '';
            calculateAndDisplayDays();
            return false;
        }
        return true;
    }

    function calculateAndDisplayDays() {
        const startDateString = fromDateInput.value;
        const endDateString = toDateInput.value;
        leaveDaysOutput.value = '';

        if (startDateString && endDateString) {
            const start = new Date(startDateString + 'T00:00:00');
            const end = new Date(endDateString + 'T00:00:00');
            let countedDays = 0;

            if (end >= start) {
                let currentDate = new Date(start.getTime());
                while (currentDate <= end) {
                    if (isWeekendLeaveSelected) {
                        if (isWeekend(currentDate)) {
                            countedDays++;
                        }
                    } else {
                        if (!isWeekend(currentDate)) {
                            countedDays++;
                        }
                    }
                    currentDate.setDate(currentDate.getDate() + 1);
                }
                leaveDaysOutput.value = countedDays > 0 ? countedDays : '';
            }
        }
    }

    function handleLeaveTypeChange() {
        const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
        const selectedLeaveTypeName = selectedOption.dataset.leaveTypeName; // This comes directly from DB via $type->name

        // Case-insensitive comparison for robustness, though WEEKEND_LEAVE_TYPE_NAME should now match exactly
        isWeekendLeaveSelected = (selectedLeaveTypeName && selectedLeaveTypeName.toLowerCase() === WEEKEND_LEAVE_TYPE_NAME.toLowerCase());

        if (isWeekendLeaveSelected) {
            weekendLeaveNote.style.display = 'block';
            if (fromDateInput.value) validateDateInputForWeekend(fromDateInput);
            if (toDateInput.value) validateDateInputForWeekend(toDateInput);
        } else {
            weekendLeaveNote.style.display = 'none';
        }
        calculateAndDisplayDays();
    }

    if (leaveTypeSelect && fromDateInput && toDateInput && leaveDaysOutput && weekendLeaveNote) {
        leaveTypeSelect.addEventListener('change', handleLeaveTypeChange);
        fromDateInput.addEventListener('change', () => {
            validateDateInputForWeekend(fromDateInput);
            calculateAndDisplayDays();
        });
        toDateInput.addEventListener('change', () => {
            validateDateInputForWeekend(toDateInput);
            calculateAndDisplayDays();
        });

        handleLeaveTypeChange();
    }
});
</script>
@endsection