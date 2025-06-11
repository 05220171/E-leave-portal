@extends('layouts.app')

@section('title', 'SSO Dashboard - Leave Records')

@section('css')
@parent {{-- Include styles from parent layout if any --}}
<style>
    /* Greeting Heading Styles */
    .hod-greeting-heading {
        font-family: Arial, sans-serif;
        font-size: 1.75rem;
        font-weight: bold;
        color: #333;
        text-align: center;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }

    .hod-greeting-subtitle {
        text-align: center;
        font-size: 0.95rem;
        color: #6c757d;
        margin-bottom: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">
    {{-- MODIFICATION START: Updated the dynamic greeting --}}
    <h1 class="hod-greeting-heading">
        @if (isset($userName) && isset($role))
            Hi, {{ Str::upper($role) }} {{ $userName }}! 👋
        @elseif (isset($userName))
            Hi, {{ $userName }}! 👋
        @else
            Welcome, SSO! 👋 {{-- Generic fallback --}}
        @endif
    </h1>
    <p class="hod-greeting-subtitle">
        Approved Leave Applications for Record Keeping
    </p>
    {{-- MODIFICATION END --}}
    
    @if(session('success'))
        <div class="custom-alert custom-alert-success" role="alert">
            {{ session('success') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="custom-alert custom-alert-danger" role="alert">
            {{ session('error') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="custom-alert custom-alert-info" role="alert">
            {{ session('info') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    {{-- The rest of your file remains unchanged --}}
    @if($leavesForRecord->isEmpty())
        <div class="custom-alert custom-alert-info text-center">
            <i class="fas fa-info-circle me-2"></i> No leave records currently requiring your attention.
        </div>
    @else
        <h3 class="mb-3 mt-4" style="font-weight: 600; color: #374151;">
            <i class="fas fa-archive me-2"></i> Leaves for Record
        </h3>
        <div class="custom-table-wrapper">
            <table class="custom-data-table">
                <thead>
                    <tr>
                        <th>Sl No.</th>
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
                    @foreach($leavesForRecord as $index => $leave)
                        @php
                            $dsaApprovalAction = $leave->approvalActions
                                ->where('acted_as_role', 'dsa')
                                ->where('action_taken', 'approved')
                                ->sortByDesc('action_at')
                                ->first();

                            $ssoRecordedAction = $leave->approvalActions->firstWhere(function ($action) {
                                return $action->acted_as_role === 'sso' && $action->action_taken === 'recorded';
                            });
                        @endphp
                        <tr>
                            <td>{{ $leavesForRecord->firstItem() + $index }}</td>
                            <td>{{ $leave->student->name ?? 'N/A' }}</td>
                            <td>{{ $leave->student->department->name ?? 'N/A' }}</td>
                            <td>{{ $leave->type->name ?? 'N/A' }}</td>
                            <td>
                                {{ $leave->start_date->format('d M Y') }}
                                <div>to {{ $leave->end_date->format('d M Y') }}</div>
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
                                <span class="status-badge status-approved">
                                    {{ Str::title(str_replace('_', ' ', $leave->overall_status)) }}
                                </span>
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
                            <td class="actions-cell">
                                @if ($ssoRecordedAction)
                                    <span class="status-badge status-recorded">
                                        <i class="fas fa-check-circle me-1"></i> Recorded
                                    </span>
                                    <small class="d-block text-muted">
                                        {{ $ssoRecordedAction->action_at->format('d M Y, H:i') }}
                                    </small>
                                    <small class="d-block text-muted">
                                        by {{ $ssoRecordedAction->user->name ?? 'SSO' }}
                                    </small>
                                @elseif ($leave->current_approver_role === 'sso' || $leave->overall_status === 'approved')
                                    <form action="{{ route('sso.leaves.mark-recorded', $leave->id) }}" method="POST" class="d-inline-form">
                                        @csrf
                                        <button type="submit" class="custom-btn-sm custom-btn-info" title="Mark as Recorded">
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

        @if($leavesForRecord->hasPages())
            <div class="mt-4 d-flex justify-content-center pagination-wrapper">
                {{ $leavesForRecord->links() }}
            </div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var alertCloseButtons = document.querySelectorAll('.custom-alert .custom-alert-close');
    alertCloseButtons.forEach(function (button) {
        if (!button.textContent) {
            button.innerHTML = '×';
        }
        button.addEventListener('click', function () {
            this.closest('.custom-alert').style.display = 'none';
        });
    });
});
</script>
@endpush