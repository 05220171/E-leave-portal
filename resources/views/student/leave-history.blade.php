{{-- resources/views/student/leave-history.blade.php --}}

@extends('layouts.app') {{-- Use the same layout as your apply_leave form --}}

@section('content')
<div class="container mt-4"> {{-- Assuming Bootstrap styling --}}
    <h2 class="page-title mb-4">My Leave History</h2>

    {{-- Display Success/Error Messages from cancel/delete actions --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($leaves->isEmpty())
        <div class="alert alert-info">
            You have not applied for any leave yet. <a href="{{ route('student.apply-leave') }}">Apply for one now?</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Applied On</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leaves as $index => $leave)
                        <tr>
                            <td>{{ $leaves->firstItem() + $index }}</td>
                            <td>
                                @if ($leave->type)
                                    {{ $leave->type->name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $leave->start_date->format('d M Y') }}</td>
                            <td>{{ $leave->end_date->format('d M Y') }}</td>
                            <td>{{ $leave->number_of_days ?? 'N/A' }}</td>
                            <td>{{ Str::limit($leave->reason, 40) }}</td>
                            <td>
                                {{-- ✅ USE overall_status HERE --}}
                                <span class="badge
                                    @if($leave->overall_status === 'approved') bg-success
                                    @elseif($leave->overall_status === 'cancelled') bg-secondary
                                    @elseif(Str::startsWith($leave->overall_status, 'rejected_by_')) bg-danger
                                    @elseif(Str::startsWith($leave->overall_status, 'awaiting_')) bg-warning text-dark
                                    @else bg-info text-dark @endif">
                                    {{ Str::title(str_replace('_', ' ', $leave->overall_status)) }}
                                </span>
                                {{-- If status is pending, show who it's pending with --}}
                                @if(Str::startsWith($leave->overall_status, 'awaiting_') && $leave->current_approver_role)
                                    <small class="d-block text-muted">Pending: {{ Str::title($leave->current_approver_role) }}</small>
                                @endif
                            </td>
                            <td>{{ $leave->created_at->format('d M Y, H:i') }}</td>
                            <td>{{ $leave->final_remarks ?: ($leave->remarks ?: 'N/A') }}</td>
                            <td>
                                {{-- ✅ USE overall_status for action logic --}}
                                @if(Str::startsWith($leave->overall_status, 'awaiting_') && $leave->overall_status !== 'cancelled')
                                    <form action="{{ route('student.cancel-leave', $leave->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning">Cancel</button>
                                    </form>
                                @endif

                                {{-- Only allow deletion of 'cancelled' leaves --}}
                                @if($leave->overall_status === 'cancelled')
                                    <form action="{{ route('student.delete-leave', $leave->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this cancelled leave record? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endif

                                {{-- Show '-' if no actions are available --}}
                                @if(!(Str::startsWith($leave->overall_status, 'awaiting_') && $leave->overall_status !== 'cancelled') && !($leave->overall_status === 'cancelled'))
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $leaves->links() }} {{-- Ensure your pagination views are styled for Bootstrap --}}
        </div>
    @endif
</div>
@endsection