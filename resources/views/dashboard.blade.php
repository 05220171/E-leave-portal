@extends('layouts.app') {{-- Use your custom layout --}}

@section('content') {{-- Define the content section --}}

    <h2 class="dashboard-title">Student Dashboard</h2>

    {{-- Container for the cards --}}
    <div class="dashboard-grid">

        {{-- Card 1: Apply Leave --}}
        <div class="dashboard-card">
            <div class="card-content">
                <h3 class="card-title">Apply for Leave</h3>
                <p class="card-description">
                    Need time off? Submit your leave request here.
                </p>
            </div>
            <div class="card-action">
                 <a href="{{ route('student.apply-leave') }}" class="card-button button-apply">
                     Go to Application
                 </a>
            </div>
        </div>

        {{-- Card 2: Leave History --}}
        <div class="dashboard-card">
            <div class="card-content">
                <h3 class="card-title">Leave History</h3>
                <p class="card-description">
                    View the details and status of your past leave requests.
                </p>
            </div>
             <div class="card-action">
                 <a href="{{ route('student.leave-history') }}" class="card-button button-history">
                     View History
                 </a>
            </div>
        </div>

        {{-- Card 3: Leave Status (Example) --}}
        <div class="dashboard-card">
            <div class="card-content">
                <h3 class="card-title">Leave Status</h3>
                <p class="card-description">
                    Check the current status of your pending leave requests.
                </p>
            </div>
             <div class="card-action">
                 {{-- Replace '#' with the actual route when created --}}
                 <a href="{{ route('student.leave-status') }}" class="card-button button-status">
                     Check Status
                 </a>
            </div>
        </div>

        {{-- Add more cards here if needed for other sidebar items --}}

    </div> {{-- End Grid Container --}}

@endsection