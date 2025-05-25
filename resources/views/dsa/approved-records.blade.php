@extends('layouts.app')

@section('title', 'DSA - My Approved Leave Records')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4 text-center"> Approved Leave Records</h1>
    <p class="text-center text-muted mb-4">Leaves you have personally approved.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($approvedLeaves->isEmpty())
        <div class="alert alert-info text-center shadow-sm">
            <i class="fas fa-info-circle me-2"></i> You have not approved any leave requests yet.
        </div>
    @else
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-check-double me-2"></i>Leaves Approved by You</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Department</th> {{-- Added Department for DSA's view --}}
                                <th>Leave Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Reason & Document</th>
                                <th>Applied On</th>
                                <th>Final Status</th>
                                <th>Your Approval Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvedLeaves as $index => $leave)
                                @php
                                    $dsaApprovalAction = $leave->approvalActions
                                        ->where('user_id', Auth::id()) // Or $dsaUser->id if passed
                                        ->where('acted_as_role', 'dsa')
                                        ->where('action_taken', 'approved')
                                        ->first();
                                @endphp
                                <tr>
                                    <td>{{ $approvedLeaves->firstItem() + $index }}</td>
                                    <td>{{ $leave->student->name ?? 'N/A' }}</td>
                                    <td>{{ $leave->student->department->name ?? 'N/A' }}</td> {{-- Display Department --}}
                                    <td>{{ $leave->type->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $leave->start_date->format('d M Y') }}
                                        <small class="text-muted d-block">to {{ $leave->end_date->format('d M Y') }}</small>
                                    </td>
                                    <td>{{ $leave->number_of_days ?? 'N/A' }}</td>
                                    <td>
                                        <span title="{{ $leave->reason }}">{{ Str::limit($leave->reason, 30) }}</span>
                                        @if ($leave->document)
                                            <a href="{{ Storage::url($leave->document) }}" target="_blank" class="d-block text-info small" title="View Document">
                                                <i class="fas fa-paperclip"></i> View Document
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $leave->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        <span class="badge
                                            @if($leave->overall_status === 'approved') bg-success
                                            @elseif($leave->overall_status === 'cancelled') bg-secondary
                                            @elseif(Str::startsWith($leave->overall_status, 'rejected_by_')) bg-danger
                                            @elseif(Str::startsWith($leave->overall_status, 'awaiting_')) bg-warning text-dark
                                            @else bg-info text-dark @endif">
                                            {{ Str::title(str_replace('_', ' ', $leave->overall_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($dsaApprovalAction)
                                            {{ $dsaApprovalAction->action_at->format('d M Y, H:i') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($approvedLeaves->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $approvedLeaves->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection