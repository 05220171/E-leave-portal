{{-- resources/views/student/leave-history.blade.php --}}

@extends('layouts.app') {{-- Use the same layout as your apply_leave form --}}

@section('content')
    <h2 class="page-title">My Leave History</h2>

    {{-- Display Success/Error Messages from cancel/delete actions --}}
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

    @if ($leaves->isEmpty())
        <div class="alert alert-info">
            You have not applied for any leave yet.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Applied On</th>
                        <th>Remarks</th> {{-- ✅ Added Remarks Column --}}
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leaves as $index => $leave)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ ucfirst($leave->leave_type) }}</td>
                            <td>{{ $leave->start_date->format('d M Y') }}</td>
                            <td>{{ $leave->end_date->format('d M Y') }}</td>
                            <td>{{ Str::limit($leave->reason, 50) }}</td>
                            <td>
                                <span class="badge
                                    @switch($leave->status)
                                        @case('approved') bg-success @break
                                        @case('cancelled') bg-secondary @break
                                        @case(Str::startsWith($leave->status, 'rejected_')) bg-danger @break
                                        @default bg-warning text-dark @break
                                    @endswitch">
                                    {{ Str::replace('_', ' ', Str::title($leave->status)) }}
                                </span>
                            </td>
                            <td>{{ $leave->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $leave->remarks ?? 'N/A' }}</td> {{-- ✅ Show remarks --}}
                            <td>
                                @php
                                    $cancellableStatuses = ['awaiting_hod_approval', 'awaiting_dsa_approval', 'awaiting_sso_approval'];
                                    $deletableStatuses = ['cancelled', 'rejected_by_hod', 'rejected_by_dsa', 'rejected_by_sso'];
                                @endphp

                                @if(in_array($leave->status, $cancellableStatuses))
                                    <form action="{{ route('student.cancel-leave', $leave->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">Cancel</button>
                                    </form>
                                @endif

                                @if(in_array($leave->status, $deletableStatuses))
                                    <form action="{{ route('student.delete-leave', $leave->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this leave record? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @endif

                                @if(!in_array($leave->status, $cancellableStatuses) && !in_array($leave->status, $deletableStatuses))
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $leaves->links() }}
        </div>
    @endif
@endsection
