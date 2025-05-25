@extends('layouts.app')

@section('title', 'HOD - My Approved Leave Records')

@section('content')
<div class="container mt-4">
    {{-- Main page heading --}}
    <h1 class="page-section-title text-center">Approved Leave Records</h1>
    <p class="text-center text-muted mb-4">Leaves you have personally approved from your department.</p>

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

    @if($approvedLeaves->isEmpty())
        <div class="custom-alert custom-alert-info text-center">
            <i class="fas fa-info-circle me-2"></i> You have not approved any leave requests yet, or no records match.
        </div>
    @else
        {{-- Heading for the table section, replacing card-header --}}
        <h3 class="mb-3 mt-4" style="font-weight: 600; color: #16a085;"> {{-- Using a green color similar to Bootstrap's bg-success text --}}
            <i class="fas fa-check-double me-2"></i> Leave Approved by You
        </h3>

        {{-- Table wrapper and table with custom classes --}}
        <div class="custom-table-wrapper">
            <table class="custom-data-table">
                <thead>
                    <tr>
                        <th>NO.</th>
                        <th>Student Name</th>
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
                            $hodApprovalAction = $leave->approvalActions
                                ->where('user_id', Auth::id())
                                ->where('acted_as_role', 'hod')
                                ->where('action_taken', 'approved')
                                ->first();
                        @endphp
                        <tr>
                            <td>{{ $approvedLeaves->firstItem() + $index }}</td>
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
                                    <a href="{{ Storage::url($leave->document) }}" target="_blank" class="d-block text-info small" title="View Document">
                                        <i class="fas fa-paperclip"></i> View Document
                                    </a>
                                @endif
                            </td>
                            <td>{{ $leave->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                {{-- Custom status badges --}}
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
                                @if($hodApprovalAction)
                                    {{ $hodApprovalAction->action_at->format('d M Y, H:i') }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($approvedLeaves->hasPages())
            <div class="mt-4 d-flex justify-content-center pagination-wrapper">
                {{ $approvedLeaves->links() }}
            </div>
        @endif
    @endif
</div>
@endsection

@section('css')
{{-- This CSS should ideally be in a global stylesheet linked in layouts.app.blade.php --}}
<style>
    /* === Global Custom Styles === */
    .page-section-title {
        font-size: 1.75rem; font-weight: 600; color: #2c3e50; margin-bottom: 1rem;
        padding-bottom: 0.5rem; border-bottom: 2px solid #ecf0f1;
    }
    h1.page-section-title { font-size: 2.25rem; /* border-bottom: none; */ }
    .text-center { text-align: center !important; }
    .text-muted { color: #6c757d !important; }
    .mb-4 { margin-bottom: 1.5rem !important; }
    .mb-3 { margin-bottom: 1rem !important; }
    .mt-4 { margin-top: 1.5rem !important; }
    .me-2 { margin-right: 0.5rem !important; }
    .d-block { display: block !important; }
    .text-info { color: #17a2b8 !important; } /* Standard Bootstrap info color - adjust if your custom palette is different */
    .small { font-size: .875em; }


    .custom-btn, .custom-btn-sm {
        display: inline-block; font-weight: 400; text-align: center; vertical-align: middle;
        user-select: none; border: 1px solid transparent; padding: 0.375rem 0.75rem;
        font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; text-decoration: none;
        transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
    .custom-btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; line-height: 1.5; border-radius: 0.2rem; }
    .custom-btn-primary { color: #fff; background-color: #3498db; border-color: #3498db; }
    .custom-btn-primary:hover { background-color: #2980b9; border-color: #217dbb; }
    .custom-btn-info { color: #fff; background-color: #1abc9c; border-color: #1abc9c; }
    .custom-btn-info:hover { background-color: #16a085; border-color: #148f77; }
    .custom-btn-warning { color: #212529; background-color: #f39c12; border-color: #f39c12; }
    .custom-btn-warning:hover { background-color: #e08e0b; border-color: #d4830a; }
    .custom-btn-danger { color: #fff; background-color: #e74c3c; border-color: #e74c3c; }
    .custom-btn-danger:hover { background-color: #c0392b; border-color: #b33426; }

    .custom-alert {
        position: relative; padding: 0.75rem 1.25rem; margin-bottom: 1rem;
        border: 1px solid transparent; border-radius: 0.25rem;
    }
    .custom-alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    .custom-alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
    .custom-alert-info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }
    .custom-alert-close {
        float: right; font-size: 1.2rem; font-weight: 700; line-height: 1; color: inherit;
        text-shadow: 0 1px 0 #fff; opacity: .5; background-color: transparent; border: 0;
        padding: 0; cursor: pointer;
    }
    .custom-alert-close:hover { opacity: .75; }

    .custom-table-wrapper {
        overflow-x: auto; background-color: #fff; border: 1px solid #dfe3e8;
        border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-top: 1rem;
    }
    .custom-data-table { width: 100%; border-collapse: collapse; }
    .custom-data-table th,
    .custom-data-table td {
        padding: 12px 15px; text-align: left; border-bottom: 1px solid #dfe3e8; vertical-align: middle;
    }
    .custom-data-table th {
        background-color: #f9fafb; font-weight: 600; color: #374151;
        text-transform: uppercase; font-size: 0.85em; letter-spacing: 0.05em;
    }
    .custom-data-table th, .custom-data-table td { border-right: 1px solid #dfe3e8; }
    .custom-data-table th:first-child, .custom-data-table td:first-child { border-left: 1px solid #dfe3e8; }
    .custom-data-table tr:first-child th { border-top: 1px solid #dfe3e8; }
    .custom-data-table th:last-child, .custom-data-table td:last-child { border-right: 0; }
    .custom-data-table tr:first-child th:first-child {border-top-left-radius: 3px;}
    .custom-data-table tr:first-child th:last-child {border-top-right-radius: 3px;}
    .custom-data-table tr:last-child td:first-child {border-bottom-left-radius: 3px;}
    .custom-data-table tr:last-child td:last-child {border-bottom-right-radius: 3px; border-bottom:0;}

    .status-badge {
        color: #fff; padding: 0.3em 0.7em; font-size: 0.8em; font-weight: 600;
        border-radius: 12px; text-transform: capitalize; display: inline-block;
    }
    .status-badge.status-approved { background-color: #2ecc71; }
    .status-badge.status-cancelled { background-color: #95a5a6; }
    .status-badge.status-rejected { background-color: #e74c3c; }
    .status-badge.status-pending { background-color: #f39c12; color: #2c3e50;}
    .status-badge.status-default { background-color: #bdc3c7; color: #2c3e50;}
    .status-badge.status-recorded { background-color: #e9f7ef; color: #198754; border: 1px solid #a6d9b8; }


    .actions-cell .custom-btn-sm { margin-right: 5px; }
    .actions-cell .custom-btn-sm:last-child { margin-right: 0; }
    .d-inline-form { display: inline-block; }

    .pagination-wrapper .pagination {
        display: flex; padding-left: 0; list-style: none; border-radius: 0.25rem; justify-content: center;
    }
    .pagination-wrapper .page-item .page-link {
        position: relative; display: block; padding: 0.5rem 0.75rem; margin-left: -1px; line-height: 1.25;
        color: #3498db; background-color: #fff; border: 1px solid #dee2e6;
    }
    .pagination-wrapper .page-item:first-child .page-link { margin-left: 0; border-top-left-radius: 0.25rem; border-bottom-left-radius: 0.25rem; }
    .pagination-wrapper .page-item:last-child .page-link { border-top-right-radius: 0.25rem; border-bottom-right-radius: 0.25rem; }
    .pagination-wrapper .page-item.active .page-link { z-index: 1; color: #fff; background-color: #3498db; border-color: #3498db; }
    .pagination-wrapper .page-item.disabled .page-link { color: #6c757d; pointer-events: none; cursor: auto; background-color: #fff; border-color: #dee2e6; }
    .pagination-wrapper .page-item:not(.active):not(.disabled) .page-link:hover {
        color: #2374ab; background-color: #e9ecef; border-color: #dee2e6;
    }

    /* Utility classes to ensure they are defined if not using Bootstrap's core CSS fully */
    .d-flex { display: flex !important; }
    .justify-content-center { justify-content: center !important; }
</style>
@stop