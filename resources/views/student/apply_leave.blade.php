@extends('layouts.app') {{-- Use your custom layout file --}}

@section('content') {{-- Define the content section for @yield('content') --}}

    <h2 class="page-title">Apply for Leave</h2> {{-- Example title class --}}

    {{-- Display Success/Error Messages --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    {{-- Display Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops! Please check the form fields:</strong>
            <ul class="mt-2" style="list-style-position: inside; padding-left: 1em;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- Card container for the form --}}
    <div class="form-card"> {{-- Use a class from your student.css or create one --}}
            {{-- Show number of leave days after submission --}}
        @if (isset($days))
            <div class="alert alert-info">
                Number of leave days: <strong>{{ $days }}</strong>
            </div>
        @endif   

        <form action="{{ route('student.store-leave') }}" method="POST" enctype="multipart/form-data" class="leave-form">
            @csrf {{-- CSRF protection token --}}

            {{-- Form Fields --}}
            <div class="form-group">
                <label for="leave_type">Leave Type:</label>
                <select name="leave_type" id="leave_type" required>
                    <option value="">-- Select Type --</option>
                    {{-- Add validation in controller for leave_type --}}
                    <option value="regular" {{ old('leave_type') == 'regular' ? 'selected' : '' }}>Regular</option>
                    <option value="emergency" {{ old('leave_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                </select>
            </div>

             <div class="form-group">
                <label for="from_date">Start Date:</label>
                {{-- Controller expects 'from_date' --}}
                <input type="date" name="from_date" id="from_date" value="{{ old('from_date') }}" required>
             </div>

             <div class="form-group">
                <label for="to_date">End Date:</label>
                 {{-- Controller expects 'to_date' --}}
                <input type="date" name="to_date" id="to_date" value="{{ old('to_date') }}" required>
             </div>

             <div class="form-group">
                <label for="leave_days">Number of Leave Days:</label>
                <input type="text" id="leave_days" name="leave_days" readonly>
            </div>


             <div class="form-group">
                <label for="reason">Reason:</label>
                <textarea name="reason" id="reason" rows="4" required>{{ old('reason') }}</textarea>
             </div>

             <div class="form-group">
                <label for="attachment">Attach Document (Optional):</label>
                <input type="file" name="attachment" id="attachment">
                {{-- Note: Controller needs logic to handle this upload --}}
             </div>

             <div class="form-actions">
                <button type="submit" class="card-button button-apply">Submit Leave Request</button>
             </div>

        </form> {{-- End of form --}}
    </div> {{-- End of form-card --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fromDate = document.getElementById('from_date');
            const toDate = document.getElementById('to_date');
            const leaveDays = document.getElementById('leave_days');

            function calculateDays() {
                const start = new Date(fromDate.value);
                const end = new Date(toDate.value);
                let totalDays = 0;

                if (fromDate.value && toDate.value && end >= start) {
                    // Loop through the date range and count weekdays
                    for (let currentDate = start; currentDate <= end; currentDate.setDate(currentDate.getDate() + 1)) {
                        const dayOfWeek = currentDate.getDay(); // Get the day of the week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
                        if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Exclude Saturday (6) and Sunday (0)
                            totalDays++;
                        }
                    }
                    leaveDays.value = totalDays;
                } else {
                    leaveDays.value = '';
                }
            }

            fromDate.addEventListener('change', calculateDays);
            toDate.addEventListener('change', calculateDays);
        });
    </script>



@endsection {{-- End the content section --}}
