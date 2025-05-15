@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Leave Details</h2>
    <p><strong>Student:</strong> {{ $leave->student->name }}</p>
    <p><strong>Reason:</strong> {{ $leave->reason }}</p>
    <p><strong>From:</strong> {{ $leave->from_date }}</p>
    <p><strong>To:</strong> {{ $leave->to_date }}</p>

    <form action="{{ route('sso.approve-leave', $leave->id) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success">Approve</button>
    </form>

    <form action="{{ route('sso.reject-leave', $leave->id) }}" method="POST" style="margin-top: 10px;">
        @csrf
        <button type="submit" class="btn btn-danger">Reject</button>
    </form>
</div>
@endsection
