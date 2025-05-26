{{-- resources/views/hod/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'HOD Dashboard - Pending Leave Applications')

@section('content')
<div class="container mt-4">
    <h1 class="page-section-title text-center">HOD Dashboard</h1>
    <p class="text-center text-muted mb-4">Pending Leave Applications for Your Department</p>

    {{-- Session Messages --}}
    @if(session('success'))
        <div class="custom-alert custom-alert-success" role="alert">
            {{ session('success') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button>
        </div>
    @endif
    @if(session('error'))
        <div class="custom-alert custom-alert-danger" role="alert">
            {{ session('error') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button>
        </div>
    @endif
     @if(session('info'))
        <div class="custom-alert custom-alert-info" role="alert">
            {{ session('info') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button>
        </div>
    @endif

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
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Reason & Document</th> {{-- Combined "Reason" and "Document" --}}
                        <th>Applied On</th>
                        <th>Status</th>
                        <th style="min-width: 200px;">Remarks</th> {{-- <<< NEW REMARKS COLUMN HEADER --}}
                        <th style="min-width: 180px;">Actions</th> {{-- Actions column --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $index => $leave)
                        <tr>
                            <td>{{ $leaves->firstItem() + $index }}</td>
                            <td>{{ $leave->student->name ?? 'N/A' }}</td>
                            <td>{{ $leave->type->name ?? 'N/A' }}</td>
                            <td>
                                {{ $leave->start_date->format('d M Y') }}
                                <small class="text-muted d-block">to {{ $leave->end_date->format('d M Y') }}</small>
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
                            {{-- NEW REMARKS INPUT COLUMN --}}
                            <td>
                                <textarea id="remarks_for_leave_{{ $leave->id }}" class="form-control form-control-sm elegant-textarea" rows="2" placeholder="Remarks (optional)..."></textarea>
                            </td>
                            {{-- ACTIONS COLUMN --}}
                            <td class="actions-cell text-center">
                                {{-- Approve Form --}}
                                <form action="{{ route('hod.approve-leave', $leave->id) }}" method="POST" class="d-inline-block me-1" onsubmit="return copyHodRemarks(this, {{ $leave->id }})">
                                    @csrf
                                    <input type="hidden" name="remarks" class="hidden-remarks-input"> {{-- For JS to populate --}}
                                    <button type="submit" class="custom-btn-sm custom-btn-success" title="Approve Leave">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>

                                {{-- Reject Form --}}
                                <form action="{{ route('hod.reject-leave', $leave->id) }}" method="POST" class="d-inline-block" onsubmit="return copyHodRemarks(this, {{ $leave->id }})">
                                    @csrf
                                    <input type="hidden" name="remarks" class="hidden-remarks-input"> {{-- For JS to populate --}}
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

        @if(method_exists($leaves, 'links') && $leaves->hasPages())
            <div class="mt-4 d-flex justify-content-center pagination-wrapper">
                {{ $leaves->links() }}
            </div>
        @endif
    @endif
</div>
@endsection


@push('scripts')
<script>
// This script ensures that when either "Approve" or "Reject" is clicked,
// the content of the remarks textarea in the same row is copied to a hidden
// input field within the specific form being submitted.
function copyHodRemarks(formElement, leaveId) {
    // Find the visible remarks textarea for this specific leave row
    const remarksTextarea = document.getElementById('remarks_for_leave_' + leaveId);

    if (remarksTextarea) {
        // Find the hidden 'remarks' input field within the form that is being submitted
        const hiddenRemarksField = formElement.querySelector('input[name="remarks"].hidden-remarks-input');
        if (hiddenRemarksField) {
            hiddenRemarksField.value = remarksTextarea.value;
        } else {
            console.warn('Hidden remarks field not found in form for leave ID: ' + leaveId);
        }
    } else {
        console.warn('Remarks textarea not found for leave ID: ' + leaveId);
    }
    return true; // Allow the form to submit
}

// Bootstrap 5 Form Validation (if you still want to use it for the remarks textarea if it becomes required)
// This will only apply to forms with the 'needs-validation' class.
// The remarks textarea itself does not have 'required' right now.
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
@endpush