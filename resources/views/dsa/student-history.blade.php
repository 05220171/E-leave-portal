{{-- resources/views/dsa/student-history.blade.php --}}

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Leave History for {{ $student->name }}</h1>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                @foreach($student->leaves as $leave)
                    <tr>
                        <td>{{ $leave->leave_type }}</td>
                        <td>{{ $leave->start_date }}</td>
                        <td>{{ $leave->end_date }}</td>
                        <td>{{ ucfirst($leave->status) }}</td>
                        <td>{{ $leave->comments ?? 'No comments' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('dsa.index') }}" class="btn btn-primary mt-3">Back to Dashboard</a>
    </div>
@endsection
