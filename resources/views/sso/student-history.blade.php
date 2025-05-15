@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ $student->name }}'s Leave History</h2>

    @if($leaves->isEmpty())
        <p>No leave records found.</p>
    @else
        <ul>
            @foreach($leaves as $leave)
                <li>
                    {{ $leave->from_date }} to {{ $leave->to_date }} — {{ $leave->status }}
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
