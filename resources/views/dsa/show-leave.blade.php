{{-- resources/views/dsa/show-leave.blade.php --}}

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Leave Application Details</h1>

        <div class="card">
            <div class="card-body">
                <h4>Student: {{ $leave->student->name }}</h4>
                <p><strong>Leave Type:</strong> {{ $leave->leave_type }}</p>
                <p><strong>Start Date:</strong> {{ $leave->start_date }}</p>
                <p><strong>End Date:</strong> {{ $leave->end_date }}</p>
                <p><strong>Reason for Leave:</strong> {{ $leave->reason }}</p>

                <h4>Additional Comments:</h4>
                <p>{{ $leave->comments ?? 'No comments provided.' }}</p>

                <form action="{{ route('dsa.approve', $leave->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Approve Leave</button>
                </form>

                <form action="{{ route('dsa.reject', $leave->id) }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-danger">Reject Leave</button>
                </form>

                <a href="{{ route('dsa.index') }}" class="btn btn-primary mt-3">Back to Dashboard</a>
            </div>
        </div>
    </div>
@endsection
