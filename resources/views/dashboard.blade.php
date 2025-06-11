@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('css')
@parent
<style>
    /* Greeting Heading Styles */
    .hod-greeting-heading {
        font-family: Arial, sans-serif;
        font-size: 1.75rem;
        font-weight: bold;
        color: #333;
        text-align: center;
        margin-bottom: 1.5rem; /* Space below heading */
        line-height: 1.3;
    }

    /* Styles for the cards */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        padding: 0 1rem 1rem 1rem; /* Padding for the grid */
    }
    .dashboard-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }
    .card-content {
        padding: 1.5rem;
    }
    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }
    .card-description {
        font-size: 0.95rem;
        color: #7f8c8d;
        line-height: 1.5;
    }
    .card-action {
        padding: 1rem 1.5rem;
        border-top: 1px solid #ecf0f1;
        text-align: right;
    }
    .card-button {
        display: inline-block;
        padding: 0.6rem 1.2rem;
        border-radius: 5px;
        text-decoration: none;
        color: #fff;
        font-weight: 500;
        transition: background-color 0.2s;
    }
    .button-apply { background-color: #3498db; }
    .button-apply:hover { background-color: #2980b9; }
    .button-history { background-color: #9b59b6; }
    .button-history:hover { background-color: #8e44ad; }
    .button-status { background-color: #1abc9c; }
    .button-status:hover { background-color: #16a085; }

</style>
@endsection


@section('content')

    {{-- MODIFICATION: Replaced the old h2 with this new dynamic greeting --}}
    <h1 class="hod-greeting-heading">
        @if (isset($userName))
            Hi, {{ $userName }}! 👋
        @else
            Welcome, Student! 👋 {{-- This is the fallback you were seeing before --}}
        @endif
    </h1>
    
    {{-- Container for the cards (your original code) --}}
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
                 <a href="{{ route('student.leave-status') }}" class="card-button button-status">
                     Check Status
                 </a>
            </div>
        </div>

    </div> {{-- End Grid Container --}}

@endsection