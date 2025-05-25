{{-- resources/views/hod/dashboard.blade.php (or your specific path) --}}
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
                        <th>Reason</th>
                        <th>Applied On</th>
                        <th>Status</th>
                        <th style="min-width: 200px; text-align: center;">Actions</th>
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
                            <td class="actions-cell text-center">
                                <form action="{{ route('hod.approve-leave', $leave->id) }}" method="POST" class="d-inline-block me-1">
                                    @csrf
                                    <button type="submit" class="custom-btn-sm custom-btn-success" title="Approve Leave">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>

                                <button type="button" class="custom-btn-sm custom-btn-danger" data-bs-toggle="modal" data-bs-target="#rejectLeaveModal-{{ $leave->id }}" title="Reject Leave">
                                    <i class="fas fa-times"></i> Reject
                                </button>

                                <!-- Simplified Elegant Reject Modal -->
                                <div class="modal fade elegant-modal" id="rejectLeaveModal-{{ $leave->id }}" tabindex="-1" aria-labelledby="rejectLeaveModalLabel-{{ $leave->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content shadow-lg">
                                            <form action="{{ route('hod.reject-leave', $leave->id) }}" method="POST" class="needs-validation" novalidate>
                                                @csrf
                                                <div class="modal-body" style="padding-top: 2rem; padding-bottom: 1.5rem; padding-left: 1.5rem; padding-right: 1.5rem;">
                                                    <textarea name="remarks" id="remarks-{{ $leave->id }}" class="form-control elegant-textarea" rows="4" placeholder="Enter rejection remarks (required)..." required></textarea>
                                                    <div class="invalid-feedback">
                                                        Rejection remarks are required.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    {{-- Apply .custom-btn-sm to modal footer buttons --}}
                                                    <button type="button" class="custom-btn-sm custom-btn-secondary" data-bs-dismiss="modal">
                                                        <i class="fas fa-ban me-1"></i>Cancel
                                                    </button>
                                                    <button type="submit" class="custom-btn-sm custom-btn-danger">
                                                        <i class="fas fa-times-circle me-1"></i>Confirm Rejection
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
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

@section('css')
<style>
    /* === Base "Taiwan" Styling === */
    .page-section-title { font-size: 1.75rem; font-weight: 600; color: #2c3e50; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #ecf0f1; }
    h1.page-section-title { font-size: 2.25rem; }
    .text-center { text-align: center !important; }
    .text-muted { color: #6c757d !important; }
    .mb-4 { margin-bottom: 1.5rem !important; }
    .mb-3 { margin-bottom: 1rem !important; }
    .mt-4 { margin-top: 1.5rem !important; }
    .me-1 { margin-right: 0.25rem !important; }
    .me-2 { margin-right: 0.5rem !important; }
    .d-block { display: block !important; }
    .d-inline-block { display: inline-block !important; }
    .text-info { color: #17a2b8 !important; }
    .small { font-size: .875em; }
    .fw-semibold { font-weight: 600 !important; }

    .custom-btn, .custom-btn-sm { display: inline-block; font-weight: 400; text-align: center; vertical-align: middle; user-select: none; border: 1px solid transparent; padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; text-decoration: none; transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
    .custom-btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; line-height: 1.5; border-radius: 0.2rem; }
    .custom-btn-primary { color: #fff; background-color: #3498db; border-color: #3498db; }
    .custom-btn-primary:hover { background-color: #2980b9; border-color: #217dbb; }
    .custom-btn-info { color: #fff; background-color: #1abc9c; border-color: #1abc9c; }
    .custom-btn-info:hover { background-color: #16a085; border-color: #148f77; }
    .custom-btn-warning { color: #212529; background-color: #f39c12; border-color: #f39c12; }
    .custom-btn-warning:hover { background-color: #e08e0b; border-color: #d4830a; }
    .custom-btn-danger { color: #fff; background-color: #e74c3c; border-color: #e74c3c; }
    .custom-btn-danger:hover { background-color: #c0392b; border-color: #b33426; }
    .custom-btn-success { color: #fff; background-color: #2ecc71; border-color: #2ecc71; }
    .custom-btn-success:hover { background-color: #28b463; border-color: #25a25a; }
    .custom-btn-secondary { color: #fff; background-color: #95a5a6; border-color: #95a5a6; }
    .custom-btn-secondary:hover { background-color: #808b8d; border-color: #717d7e; }

    .custom-alert { position: relative; padding: 0.75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: 0.25rem; }
    /* ... other alert styles ... */
    .custom-alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    .custom-alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
    .custom-alert-info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }
    .custom-alert-close { float: right; font-size: 1.2rem; font-weight: 700; line-height: 1; color: inherit; text-shadow: 0 1px 0 #fff; opacity: .5; background-color: transparent; border: 0; padding: 0; cursor: pointer; }
    .custom-alert-close:hover { opacity: .75; }

    .custom-table-wrapper { overflow-x: auto; background-color: #fff; border: 1px solid #dfe3e8; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-top: 1rem; }
    .custom-data-table { width: 100%; border-collapse: collapse; }
    /* ... other table styles ... */
    .custom-data-table th, .custom-data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #dfe3e8; vertical-align: middle; }
    .custom-data-table th { background-color: #f9fafb; font-weight: 600; color: #374151; text-transform: uppercase; font-size: 0.85em; letter-spacing: 0.05em; }
    .custom-data-table th, .custom-data-table td { border-right: 1px solid #dfe3e8; }
    .custom-data-table th:first-child, .custom-data-table td:first-child { border-left: 1px solid #dfe3e8; }
    .custom-data-table tr:first-child th { border-top: 1px solid #dfe3e8; }
    .custom-data-table th:last-child, .custom-data-table td:last-child { border-right: 0; }
    .custom-data-table tr:first-child th:first-child {border-top-left-radius: 3px;}
    .custom-data-table tr:first-child th:last-child {border-top-right-radius: 3px;}
    .custom-data-table tr:last-child td:first-child {border-bottom-left-radius: 3px;}
    .custom-data-table tr:last-child td:last-child {border-bottom-right-radius: 3px; border-bottom:0;}

    .status-badge { color: #fff; padding: 0.3em 0.7em; font-size: 0.8em; font-weight: 600; border-radius: 12px; text-transform: capitalize; display: inline-block; }
    /* ... other status badge styles ... */
    .status-badge.status-approved { background-color: #2ecc71; }
    .status-badge.status-cancelled { background-color: #95a5a6; }
    .status-badge.status-rejected { background-color: #e74c3c; }
    .status-badge.status-pending { background-color: #f39c12; color: #2c3e50;}
    .status-badge.status-default { background-color: #bdc3c7; color: #2c3e50;}

    .actions-cell .custom-btn-sm, .actions-cell .d-inline-block { margin-bottom: 3px; margin-top: 3px; }

    .pagination-wrapper .pagination { display: flex; padding-left: 0; list-style: none; border-radius: 0.25rem; justify-content: center; }
    /* ... other pagination styles ... */
    .pagination-wrapper .page-item .page-link { position: relative; display: block; padding: 0.5rem 0.75rem; margin-left: -1px; line-height: 1.25; color: #3498db; background-color: #fff; border: 1px solid #dee2e6; }
    .pagination-wrapper .page-item:first-child .page-link { margin-left: 0; border-top-left-radius: 0.25rem; border-bottom-left-radius: 0.25rem; }
    .pagination-wrapper .page-item:last-child .page-link { border-top-right-radius: 0.25rem; border-bottom-right-radius: 0.25rem; }
    .pagination-wrapper .page-item.active .page-link { z-index: 1; color: #fff; background-color: #3498db; border-color: #3498db; }
    .pagination-wrapper .page-item.disabled .page-link { color: #6c757d; pointer-events: none; cursor: auto; background-color: #fff; border-color: #dee2e6; }
    .pagination-wrapper .page-item:not(.active):not(.disabled) .page-link:hover { color: #2374ab; background-color: #e9ecef; border-color: #dee2e6; }


    /* === Simplified Elegant Modal Styling === */
    .elegant-modal .modal-content {
        border-radius: 0.5rem; border: none;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
    }
    .elegant-modal .modal-body {
        padding-top: 2rem; padding-bottom: 1.5rem;
        padding-left: 1.5rem; padding-right: 1.5rem;
        background-color: #f8f9fa;
    }
    .elegant-textarea,
    .elegant-modal .form-control {
        border-radius: 0.25rem; border: 1px solid #ced4da;
        padding: 0.75rem; font-size: 0.95rem; line-height: 1.5;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }
    .elegant-textarea:focus,
    .elegant-modal .form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    /* Modal Footer with smaller buttons */
    .elegant-modal .modal-footer {
        display: flex; justify-content: flex-end; align-items: center;
        background-color: #e9ecef; border-top: 1px solid #dee2e6;
        padding: 0.75rem 1.5rem;
        border-bottom-left-radius: calc(0.5rem - 1px);
        border-bottom-right-radius: calc(0.5rem - 1px);
    }
    /* Spacing for .custom-btn-sm when used in modal footer */
    .elegant-modal .modal-footer .custom-btn-sm + .custom-btn-sm {
        margin-left: 0.5rem; /* Adjust spacing as needed for smaller buttons */
    }


    /* === Bootstrap 5 Validation Styling (Customized) === */
    .needs-validation .form-control:invalid,
    .form-control.is-invalid { border-color: #e74c3c; }
    .needs-validation .form-control:invalid:focus,
    .form-control.is-invalid:focus { box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25); }
    .invalid-feedback { display: none; width: 100%; margin-top: .25rem; font-size: .875em; color: #e74c3c; }
    .was-validated .form-control:invalid ~ .invalid-feedback,
    .form-control.is-invalid ~ .invalid-feedback { display: block; }

    /* Fallback btn-close styling */
    .btn-close { box-sizing: content-box; width: 1em; height: 1em; padding: .25em .25em; color: #000; background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat; border: 0; border-radius: .25rem; opacity: .5; }
    .btn-close:hover { opacity: .75; }
    .btn-close:focus { outline: 0; box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); opacity: 1; }
</style>
@stop

@push('scripts')
<script>
// Standard Bootstrap 5 Form Validation
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