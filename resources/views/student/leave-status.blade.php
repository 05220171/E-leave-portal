{{-- resources/views/student/leave-status.blade.php --}}

@extends('layouts.app') {{-- Use your main layout file --}}

@section('content')
    <h2 class="page-title">My Pending Leave Status</h2>

    {{-- Display Success/Error Messages if needed (e.g., if actions were possible here) --}}
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

    {{-- Check if the $activeLeaves collection passed from the controller is empty --}}
    @if ($activeLeaves->isEmpty())
        <div class="alert alert-info">
            You have no active or pending leave requests at the moment.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-bordered"> {{-- Use your table classes --}}
                <thead>
                    <tr>
                        <th></th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Current Status</th>
                        <th>Applied On</th>
                         {{-- Optional: Actions column if you want cancel here too --}}
                         <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loop through the $activeLeaves passed from the controller --}}
                    @foreach ($activeLeaves as $index => $leave)
                        <tr>
                            {{-- Calculate index based on pagination --}}
                            <td>{{ $activeLeaves->firstItem() + $index }}</td>
                            <td>{{ ucfirst($leave->leave_type) }}</td>
                            <td>{{ $leave->start_date->format('d M Y') }}</td>
                            <td>{{ $leave->end_date->format('d M Y') }}</td>
                            <td>{{ Str::limit($leave->reason, 50) }}</td>
                            <td>
                                {{-- Use the same status badge logic as leave-history --}}
                                <span class="badge {{-- Add classes based on status --}}
                                    @switch($leave->status)
                                        @case('approved') bg-success @break {{-- Should technically not appear based on query --}}
                                        @case('cancelled') bg-secondary @break {{-- Should technically not appear based on query --}}
                                        @case(Str::startsWith($leave->status, 'rejected_')) bg-danger @break {{-- Should technically not appear --}}
                                        @default bg-warning text-dark @break {{-- Pending states --}}
                                    @endswitch">
                                    {{ Str::replace('_', ' ', Str::title($leave->status)) }}
                                </span>
                            </td>
                            <td>{{ $leave->created_at->format('d M Y H:i') }}</td>
                            {{-- Optional Actions Column - Example Cancel Button --}}
                            <td>
                                @php
                                    $cancellableStatuses = ['awaiting_hod_approval', 'awaiting_dsa_approval', 'awaiting_sso_approval'];
                                @endphp
                                @if(in_array($leave->status, $cancellableStatuses))
                                    <form action="{{ route('student.cancel-leave', $leave->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">Cancel</button>
                                    </form>
                                @else
                                    - {{-- Show hyphen if not cancellable --}}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination Links --}}
        <div class="mt-3">
            {{ $activeLeaves->links() }}
        </div>
    @endif

@endsection