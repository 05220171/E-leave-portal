@extends('layouts.app')

@section('title', 'DSA Dashboard - Pending Leave Applications')

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
            Welcome, DSA! 👋 {{-- Generic fallback --}}
        @endif
    </h1>
    <p class="hod-greeting-subtitle">
        Pending Leave Applications for Your Review
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
    @if($leaves->isEmpty())
        <div class="custom-alert custom-alert-info text-center">
            <i class="fas fa-info-circle me-2"></i> No leave requests currently awaiting your approval.
        </div>
    @else
        <h3 class="mb-3 mt-4" style="font-weight: 600; color: #2980b9;">
            <i class="fas fa-clipboard-list me-2"></i> Pending Approvals
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
                        <th>Applied On</th>
                        <th>Status</th>
                        <th style="min-width: 200px;">Remarks</th>
                        <th style="min-width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $index => $leave)
                        <tr>
                            <td>{{ $leaves->firstItem() + $index }}</td>
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
                                    <a href="{{ Storage::url($leave->document) }}" target="_blank" class="d-block text-info small" title="View Document"><i class="fas fa-paperclip"></i> View Document</a>
                                @endif
                            </td>
                            <td>{{ $leave->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                <span class="status-badge
                                    @if($leave->overall_status === 'approved') status-approved
                                    @elseif($leave->overall_status === 'cancelled') status-cancelled
                                    @elseif(Str::startsWith($leave->overall_status, 'rejected_by_')) status-rejected
                                    @elseif(Str::startsWith($leave->overall_status, 'awaiting_')) status-pending
                                    @else status-default @endif">
                                    {{ Str::title(str_replace('_', ' ', $leave->overall_status)) }}
                                </span>
                            </td>
                            <td>
                                <textarea id="remarks_for_leave_dsa_{{ $leave->id }}" class="form-control form-control-sm elegant-textarea" rows="2" placeholder="Remarks (optional)..."></textarea>
                            </td>
                            <td class="actions-cell text-center">
                                <form action="{{ route('dsa.approve', $leave->id) }}" method="POST" class="d-inline-block me-1" onsubmit="return copyDsaRemarks(this, {{ $leave->id }})">
                                    @csrf
                                    <input type="hidden" name="remarks" class="hidden-remarks-input">
                                    <button type="submit" class="custom-btn-sm custom-btn-success" title="Approve Leave">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <form action="{{ route('dsa.reject', $leave->id) }}" method="POST" class="d-inline-block" onsubmit="return copyDsaRemarks(this, {{ $leave->id }})">
                                    @csrf
                                    <input type="hidden" name="remarks" class="hidden-remarks-input">
                                    <button type="submit" class="custom-btn-sm custom-btn-danger" title="Reject Leave">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($leaves->hasPages())
            <div class="mt-4 d-flex justify-content-center pagination-wrapper">
                {{ $leaves->links() }}
            </div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
function copyDsaRemarks(formElement, leaveId) {
    const remarksTextarea = document.getElementById('remarks_for_leave_dsa_' + leaveId);
    if (remarksTextarea) {
        const hiddenRemarksField = formElement.querySelector('input[name="remarks"].hidden-remarks-input');
        if (hiddenRemarksField) {
            hiddenRemarksField.value = remarksTextarea.value;
        }
    }
    return true;
}
</script>
@endpush