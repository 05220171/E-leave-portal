@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Leave Details</h2>
    <p><strong>Student:</strong> {{ $leave->student->name }}</p>
    <p><strong>Type:</strong> {{ ucfirst($leave->type) }}</p>
    <p><strong>Start Date:</strong> {{ $leave->start_date }}</p>
    <p><strong>End Date:</strong> {{ $leave->end_date }}</p>
    <p><strong>Reason:</strong> {{ $leave->reason }}</p>
    <p><strong>Status:</strong> {{ $leave->status }}</p>
    @if($leave->document)
        <p><strong>Document:</strong> <a href="{{ asset('storage/' . $leave->document) }}" target="_blank">View Document</a></p>
    @endif

    <form action="{{ route('hod.leave.update', $leave->id) }}" method="POST" class="d-inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="action" value="approved">
        <button class="btn btn-success">Approve</button>
    </form>
    <form action="{{ route('hod.leave.update', $leave->id) }}" method="POST" class="d-inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="action" value="rejected">
        <button class="btn btn-danger">Reject</button>
    </form>
</div>
@endsection
