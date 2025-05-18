@extends('layouts.app')

@section('title', 'SSO Dashboard - Leave Records')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4 text-center">SSO Dashboard</h1>
    <p class="text-center text-muted mb-4">Approved Leave Applications for Record Keeping</p>

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
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($leavesForRecord->isEmpty())
        <div class="alert alert-info text-center shadow-sm">
            <i class="fas fa-info-circle me-2"></i> No leave records currently requiring your attention.
        </div>
    @else
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-archive me-2"></i> Leaves for Record</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Department</th>
                                <th>Leave Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Reason & Document</th>
                                <th>Status</th>
                                <th>DSA Approved On</th>
                                <th style="min-width: 200px;">Action / Recorded</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $ssoUserId = Auth::id(); @endphp
                            @foreach($leavesForRecord as $index => $leave)
                                @php
                                    $dsaApprovalAction = $leave->approvalActions
                                        ->where('acted_as_role', 'dsa')
                                        ->where('action_taken', 'approved')
                                        ->sortByDesc('action_at')
                                        ->first();

                                    // Correctly check if SSO has recorded this leave
                                    $ssoRecordedActions = $leave->approvalActions
                                        ->filter(function ($action) use ($ssoUserId) {
                                            return $action->user_id == $ssoUserId &&
                                                   $action->acted_as_role === 'sso' &&
                                                   $action->action_taken === 'recorded';
                                        });
                                    $ssoHasRecorded = $ssoRecordedActions->isNotEmpty();
                                    $ssoRecordedAt = $ssoHasRecorded ? $ssoRecordedActions->first()->action_at->format('d M Y H:i') : null;
                                @endphp
                                <tr>
                                    <td>{{ $leavesForRecord->firstItem() + $index }}</td>
                                    <td>{{ $leave->student->name ?? 'N/A' }}</td>
                                    <td>{{ $leave->student->department->name ?? 'N/A' }}</td>
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
                                    <td>
                                        <span class="badge bg-success">{{ Str::title(str_replace('_', ' ', $leave->overall_status)) }}</span>
                                        @if($leave->current_approver_role === 'sso' && $leave->overall_status === 'awaiting_sso_record_keeping')
                                            <small class="d-block text-muted">Pending Your Record</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dsaApprovalAction)
                                            {{ $dsaApprovalAction->action_at->format('d M Y, H:i') }}
                                            <small class="d-block text-muted">by {{ $dsaApprovalAction->user->name ?? 'DSA' }}</small>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if ($ssoHasRecorded)
                                            <span class="badge bg-light text-success border border-success">
                                                <i class="fas fa-check-circle me-1"></i> Recorded
                                            </span>
                                            @if($ssoRecordedAt)
                                            <small class="d-block text-muted">
                                                {{ $ssoRecordedAt }}
                                            </small>
                                            @endif
                                        @elseif ($leave->current_approver_role === 'sso' || $leave->overall_status === 'approved')
                                            <form action="{{ route('sso.leaves.mark-recorded', $leave->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                {{--
                                                <textarea name="sso_remarks" class="form-control form-control-sm mb-1" rows="1" placeholder="Optional remarks..."></textarea>
                                                --}}
                                                <button type="submit" class="btn btn-info btn-sm" title="Mark as Recorded">
                                                    <i class="fas fa-save"></i> Mark as Recorded
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($leavesForRecord->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $leavesForRecord->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection