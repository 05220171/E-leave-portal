@extends('layouts.app')

@section('title', 'SSO Dashboard - Leave Records')

@section('css')
<style>
    /* Greeting and Subtitle Styles */
    .sso-greeting-heading {
        font-family: Arial, sans-serif; font-size: 1.8rem; font-weight: bold;
        color: #333; text-align: center; margin-bottom: 0.25rem;
    }
    .sso-greeting-subtitle {
        text-align: center; font-size: 0.95rem; color: #6c757d; margin-bottom: 1.5rem;
    }

    /* "Leaves for Record" Heading Style - using page-section-title for consistency */
    .page-section-title { /* Applied to "Leaves for Record" h3 */
        font-size: 1.75rem; font-weight: 600; color: #2c3e50;
        margin-bottom: 1rem; padding-bottom: 0.5rem;
        /* border-bottom: 2px solid #ecf0f1; */ /* Optional border */
        display: flex; align-items: center;
    }
    .page-section-title i { margin-right: 0.5rem; color: #3498db; }


    /* Search Bar Styling - Inspired by "Manage Events" screenshot */
    .search-form-container {
        background-color: #fff; /* Or transparent if preferred */
        padding: 0.75rem 0; /* Reduced padding to align with table if card has padding */
        border-radius: 6px;
        /* box-shadow: 0 1px 3px rgba(0,0,0,0.07); */ /* Optional shadow */
        margin-bottom: 1.5rem;
    }
    .search-form-container .input-group .form-control {
        border-right: none;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        padding: 0.55rem 0.9rem; /* Slightly larger padding for input */
        font-size: 0.95rem; /* Larger font for input */
        border-color: #ced4da; /* Standard border */
    }
    .search-form-container .input-group .btn-search {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        background-color: #0d6efd; /* Bootstrap Primary Blue */
        border-color: #0d6efd;
        color: #fff;
        padding: 0.55rem 0.9rem; /* Match input padding */
    }
    .search-form-container .input-group .btn-search:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    .search-form-container .btn-clear-search {
        font-size: 0.85rem;
        padding: 0.55rem 0.75rem;
        margin-left: 0.5rem;
    }

    /* Table Styling - Re-applying HOD's custom table styles */
    .custom-table-wrapper { /* This class should wrap the table */
        overflow-x: auto; background-color: #fff; border: 1px solid #dfe3e8;
        border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-top: 0rem; /* Align with search bar */
    }
    .custom-data-table { /* Apply this class to the table */
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0; /* Important if card-body has p-0 */
    }
    .custom-data-table thead th {
        background-color: #f9fafb; font-weight: 600; color: #374151;
        text-transform: uppercase; font-size: 0.85em; /* HOD's header font size */
        letter-spacing: 0.05em;
        padding: 12px 15px; /* HOD's header padding */
        text-align: left;
        border-bottom: 1px solid #dfe3e8; /* HOD's style */
        border-right: 1px solid #dfe3e8;
        white-space: nowrap;
    }
    .custom-data-table thead th:first-child { border-left: 1px solid #dfe3e8; }
    .custom-data-table thead th:last-child { border-right: none; }


    .custom-data-table tbody td {
        padding: 12px 15px; /* HOD's cell padding */
        vertical-align: middle;
        border-bottom: 1px solid #dfe3e8;
        border-right: 1px solid #dfe3e8;
        font-size: 0.9rem;  /* LARGER FONT for cell data, adjust as needed */
        color: #495057;
    }
    .custom-data-table tbody td:first-child { border-left: 1px solid #dfe3e8; }
    .custom-data-table tbody td:last-child { border-right: none; }

    .custom-data-table tbody tr:hover {
        background-color: #f1f3f5;
    }
    .custom-data-table .text-center { text-align: center; }
    .custom-data-table .text-muted { color: #6c757d; }
    .custom-data-table .small { font-size: 0.875em; } /* HOD's small size */


    /* Status Badges - from your HOD custom styles */
    .status-badge {
        color: #fff; padding: 0.3em 0.7em; font-size: 0.8em; font-weight: 600;
        border-radius: 12px; text-transform: capitalize; display: inline-block;
    }
    .status-badge.status-approved,
    .status-badge.status-approved-recorded { /* Combined style for approved states */
        background-color: #2ecc71; /* Green from HOD */
    }
    .status-badge.status-pending-record {
        background-color: #f39c12; /* Orange from HOD's pending */
        color: #2c3e50;
    }
    .status-badge i { margin-right: 0.3em;}


    /* View Document Link Styling */
    .view-document-link {
        font-size: 0.875em; /* HOD's small size */
        color: #3498db; /* Blue from HOD custom buttons */
        text-decoration: none;
    }
    .view-document-link:hover {
        text-decoration: underline;
    }
    .view-document-link i {
        margin-right: 0.25rem;
    }

    /* Alert styling (using your custom classes) */
    .custom-alert { position: relative; padding: 0.75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: 0.25rem; }
    .custom-alert-success { color: #0f5132; background-color: #d1e7dd; border-color: #badbcc; }
    .custom-alert-info { color: #052c65; background-color: #cfe2ff; border-color: #b6d4fe; }
    .custom-alert-close { float: right; font-size: 1.2rem; font-weight: 700; line-height: 1; color: inherit; text-shadow: 0 1px 0 #fff; opacity: .5; background-color: transparent; border: 0; padding: 0; cursor: pointer; }
    .custom-alert-close:hover { opacity: .75; }

    /* Pagination to match HOD's style */
    .pagination-wrapper .pagination { display: flex; padding-left: 0; list-style: none; border-radius: 0.25rem; justify-content: center; }
    .pagination-wrapper .page-item .page-link { position: relative; display: block; padding: 0.5rem 0.75rem; margin-left: -1px; line-height: 1.25; color: #3498db; background-color: #fff; border: 1px solid #dee2e6; }
    .pagination-wrapper .page-item:first-child .page-link { margin-left: 0; border-top-left-radius: 0.25rem; border-bottom-left-radius: 0.25rem; }
    .pagination-wrapper .page-item:last-child .page-link { border-top-right-radius: 0.25rem; border-bottom-right-radius: 0.25rem; }
    .pagination-wrapper .page-item.active .page-link { z-index: 1; color: #fff; background-color: #3498db; border-color: #3498db; }
    .pagination-wrapper .page-item.disabled .page-link { color: #6c757d; pointer-events: none; cursor: auto; background-color: #fff; border-color: #dee2e6; }
    .pagination-wrapper .page-item:not(.active):not(.disabled) .page-link:hover { color: #2374ab; background-color: #e9ecef; border-color: #dee2e6; }

</style>
@endsection

@section('content')
<div class="container-fluid mt-4 px-xl-5">
    <h1 class="sso-greeting-heading">
        @if (isset($userName) && isset($role))
            Hi, {{ Str::upper($role) }} {{ $userName }}! 👋
        @else
            Welcome, SSO! 👋
        @endif
    </h1>
    <p class="sso-greeting-subtitle">
        Approved Leave Applications for Record Keeping
    </p>

    <h3 class="page-section-title"> {{-- Using page-section-title for "Leaves for Record" --}}
        <i class="fas fa-archive"></i> Leaves for Record
    </h3>

    <form method="GET" action="{{ route('sso.dashboard') }}" class="mb-4" role="search">
        {{-- Main search input and button on one line --}}
        <div class="d-flex">
            <input class="form-control me-2 form-control-lg" type="search" name="search"
                   placeholder="Search by Student, Dept, Program, Leave Type..."
                   value="{{ request('search') }}" aria-label="Search">
            <button class="btn btn-outline-primary btn-lg" type="submit" title="Search">
                <i class="fas fa-search me-1"></i>Search
            </button>
        </div>

        {{-- "Clear search" button on a new line below, if a search is active --}}
        @if(request('search'))
            <div class="mt-2 text-start"> {{-- text-start to align it left, or text-end for right --}}
                <a href="{{ route('sso.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times-circle me-1"></i>Clear Search
                </a>
            </div>
        @endif
    </form>

    @if(session('success'))
        <div class="custom-alert custom-alert-success mt-3" role="alert">
            {{ session('success') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button>
        </div>
    @endif
    @if(session('error'))
        <div class="custom-alert custom-alert-danger mt-3" role="alert">
            {{ session('error') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button>
        </div>
    @endif


    @if($leavesForRecord->isEmpty())
        <div class="custom-alert custom-alert-info text-center mt-3">
            <i class="fas fa-info-circle me-2"></i>
            @if(request('search'))
                No leave records found matching your search criteria.
            @else
                No leave records available at the moment.
            @endif
        </div>
    @else
        <div class="custom-table-wrapper mt-3"> {{-- Use custom-table-wrapper --}}
            <table class="custom-data-table">   {{-- Use custom-data-table --}}
                <thead>
                    <tr>
                        <th class="text-center">Sl No.</th> {{-- Use Sl No. as per HOD --}}
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Program</th>
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th class="text-center">Days</th>
                        <th>Reason & Document</th>
                        <th class="text-center">Overall Status</th>
                        <th>Approved On</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @php $loggedInSsoUserId = Auth::id(); @endphp
                    @foreach($leavesForRecord as $index => $leave)
                        @php
                            $dsaApprovalAction = $leave->approvalActions
                                ->where('acted_as_role', 'dsa')
                                ->where('action_taken', 'approved')
                                ->sortByDesc('action_at')
                                ->first();
                            $ssoRecordedAction = $leave->approvalActions
                                ->where('acted_as_role', 'sso')
                                ->where('action_taken', 'recorded')
                                ->sortByDesc('action_at')
                                ->first();
                        @endphp
                        <tr>
                            <td class="text-center">{{ $leavesForRecord->firstItem() + $index }}</td>
                            <td>{{ $leave->student->name ?? 'N/A' }}</td>
                            <td>{{ $leave->student->department->name ?? 'N/A' }}</td>
                            <td>{{ $leave->student->program->name ?? ($leave->student->old_program_name_text ?? 'N/A') }}</td>
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
                            <td class="text-center">
                                {{-- Use status-badge for consistency --}}
                                <span class="status-badge status-{{ str_replace(['_by_dsa', '_by_hod'], '', str_replace('awaiting_sso_record_keeping', 'pending-record', strtolower($leave->overall_status))) }}">
                                    {{ Str::title(str_replace('_', ' ', $leave->overall_status)) }}
                                </span>
                            </td>
                            <td>
                                @if($dsaApprovalAction)
                                    {{ $dsaApprovalAction->action_at->format('d M Y, H:i') }}
                                    <small class="text-muted d-block">by {{ $dsaApprovalAction->user->name ?? 'DSA' }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if($leavesForRecord->hasPages())
        <div class="mt-4 d-flex justify-content-center pagination-wrapper">
            {{ $leavesForRecord->links() }}
        </div>
    @endif
    @endif
</div>
@endsection