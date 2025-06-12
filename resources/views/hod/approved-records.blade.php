@extends('layouts.app')

@section('title', 'HOD - My Approved Leave Records')

@section('css')
{{--
    IMPORTANT: Ideally, ALL these styles (page title, search bar, custom table,
    status badges, alerts, pagination) should be in your GLOBAL CSS file
    (e.g., public/css/student.css or a new admin-common.css) linked in layouts.app.blade.php.
    Then, you can REMOVE this entire @section('css') block.
    I'm including them here so this Blade file is self-contained for this specific example,
    assuming you might still be using page-specific styles for now.
--}}
<style>
    /* Main Page Title Style from your HOD dashboard */
    .page-section-title {
        font-size: 1.75rem; font-weight: 600; color: #2c3e50;
        margin-bottom: 1rem; padding-bottom: 0.5rem;
        /* border-bottom: 2px solid #ecf0f1; */ /* Optional: remove if you don't want underline for H3 too */
        display: flex; align-items: center;
    }
    h1.page-section-title { /* For the main H1 title */
        font-size: 2.25rem; text-align: center; display: block;
        border-bottom: 2px solid #ecf0f1; /* Keep for main H1 title */
    }
    h3.page-section-title i { margin-right: 0.5rem; color: #16a085; } /* Green icon for "Approved" heading */

    /* Search Bar Styling - Copied from SSO/Manage Events */
    .search-form-container {
        background-color: #fff; padding: 0.75rem 1rem; border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.07); margin-bottom: 1.5rem;
    }
    .search-form-container .input-group .form-control {
        border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;
        padding: 0.55rem 0.9rem; font-size: 0.9rem; border-color: #ced4da;
    }
    .search-form-container .input-group .form-control:focus {
        border-color: #3498db; box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    .search-form-container .input-group .btn-search {
        border-top-left-radius: 0; border-bottom-left-radius: 0;
        background-color: #0d6efd; border-color: #0d6efd; color: #fff;
        padding: 0.55rem 0.9rem;
    }
    .search-form-container .input-group .btn-search:hover {
        background-color: #0b5ed7; border-color: #0a58ca;
    }
    .search-form-container .btn-clear-search {
        font-size: 0.85rem; padding: 0.55rem 0.75rem; margin-left: 0.5rem;
    }

    /* Table Styling - Using HOD's custom table styles */
    .custom-table-wrapper { /* ... (same as your HOD dashboard CSS) ... */
        overflow-x: auto; background-color: #fff; border: 1px solid #dfe3e8;
        border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-top: 0;
    }
    .custom-data-table { /* ... (same as your HOD dashboard CSS) ... */
        width: 100%; border-collapse: collapse; margin-bottom: 0;
    }
    .custom-data-table thead th { /* ... (same as your HOD dashboard CSS) ... */
        background-color: #f9fafb; font-weight: 600; color: #374151;
        text-transform: uppercase; font-size: 0.85em; letter-spacing: 0.05em;
        padding: 12px 15px; text-align: left;
        border-bottom: 1px solid #dfe3e8; border-right: 1px solid #dfe3e8;
        white-space: nowrap;
    }
    .custom-data-table thead th:first-child { border-left: 1px solid #dfe3e8; }
    .custom-data-table thead th:last-child { border-right: none; }

    .custom-data-table tbody td { /* ... (same as your HOD dashboard CSS) ... */
        padding: 12px 15px; vertical-align: middle;
        border-bottom: 1px solid #dfe3e8; border-right: 1px solid #dfe3e8;
        font-size: 0.9rem; color: #495057;
    }
    .custom-data-table tbody td:first-child { border-left: 1px solid #dfe3e8; }
    .custom-data-table tbody td:last-child { border-right: none; }
    .custom-data-table tbody tr:last-child td { border-bottom: none; }
    .custom-data-table tbody tr:hover { background-color: #f1f3f5; }
    .custom-data-table .text-center { text-align: center; }
    .custom-data-table .text-muted { color: #6c757d; }
    .custom-data-table .small { font-size: 0.875em; }

    /* Status Badges & View Document Link - from your HOD custom styles */
    .status-badge { /* ... (same as your HOD dashboard CSS) ... */ }
    .status-badge.status-approved { background-color: #2ecc71; }
    /* ... other status badge colors from HOD CSS ... */
    .view-document-link { /* ... (same as your HOD dashboard CSS) ... */ }

    /* Alert styling */
    .custom-alert { /* ... (same as your HOD dashboard CSS) ... */ }
    .custom-alert-success { /* ... */ } .custom-alert-info { /* ... */ } .custom-alert-close { /* ... */ }

    /* Pagination to match HOD's style */
    .pagination-wrapper .pagination { /* ... (same as your HOD dashboard CSS) ... */ }

</style>
@endsection

@section('content')
<div class="container mt-4">
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

    {{-- Search Form - Styled like SSO's --}}
    <form method="GET" action="{{ route('hod.approved-records') }}" class="mb-4" role="search">
        <div class="d-flex">
            <input class="form-control me-2 form-control-lg" type="search" name="search"
                   placeholder="Search by Student Name, Leave Type, Reason..." value="{{ request('search') }}" aria-label="Search">
                   <button class="btn btn-outline-primary btn-lg" type="submit" title="Search">
                <i class="fas fa-search me-1"></i>Search
            </button>
        </div>
        @if(request('search'))
            <div class="mt-2 text-start">
                <a href="{{ route('hod.approved-records') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times-circle me-1"></i>Clear Search
                </a>
            </div>
        @endif
    </form>
    

    @if($approvedLeaves->isEmpty())
        <div class="custom-alert custom-alert-info text-center mt-3">
            <i class="fas fa-info-circle me-2"></i>
            @if(request('search'))
                No approved leave records found matching your search criteria.
            @else
                You have not approved any leave requests yet.
            @endif
        </div>
    @else
        <h3 class="page-section-title mt-4" style="font-size: 1.5rem; border-bottom: none;">
            <i class="fas fa-check-double"></i> Leaves Approved by You
        </h3>
        <div class="custom-table-wrapper mt-2">
            <table class="custom-data-table">
                <thead>
                    <tr>
                        <th class="text-center">Sl No.</th>
                        <th>Student Name</th>
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th class="text-center">Days</th>
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
                            <td class="text-center">{{ $approvedLeaves->firstItem() + $index }}</td>
                            <td>{{ $leave->student->name ?? 'N/A' }}</td>
                            <td>{{ $leave->type->name ?? 'N/A' }}</td>
                            <td>
                                {{ $leave->start_date->format('d M Y') }}
                                <small class="text-muted d-block">to {{ $leave->end_date->format('d M Y') }}</small>
                            </td>
                            <td class="text-center">{{ $leave->number_of_days ?? 'N/A' }}</td>
                            <td>
                                <span title="{{ $leave->reason }}">{{ Str::limit($leave->reason, 25) }}</span>
                                @if ($leave->document)
                                    <a href="{{ Storage::url($leave->document) }}" target="_blank" class="d-block view-document-link" title="View Document">
                                        <i class="fas fa-paperclip"></i> View Document
                                    </a>
                                @endif
                            </td>
                            <td>{{ $leave->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-center">
                                <span class="status-badge status-{{ str_replace('_', '-', strtolower($leave->overall_status)) }}">
                                    {{ Str::title(str_replace('_', ' ', $leave->overall_status)) }}
                                </span>
                            </td>
                            <td>
                                @if($hodApprovalAction)
                                    {{ $hodApprovalAction->action_at->format('d M Y, H:i') }}
                                @else
                                    N/A {{-- Should ideally always find one if this leave is in this list --}}
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
