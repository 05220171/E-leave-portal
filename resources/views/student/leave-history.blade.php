{{-- resources/views/student/leave-history.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container mt-4"> {{-- Bootstrap container is fine --}}
    {{-- Use your custom page title class if desired, or style h2 directly --}}
    <h2 class="page-section-title">Applied Leave Status</h2> {{-- Example: Using custom title --}}

    @if (session('success'))
        {{-- Use your custom alert classes --}}
        <div class="custom-alert custom-alert-success" role="alert">
            {{ session('success') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button> {{-- Assuming you want Bootstrap's JS to close it, or style your own close button --}}
        </div>
    @endif
    @if (session('error'))
        {{-- Use your custom alert classes --}}
        <div class="custom-alert custom-alert-danger" role="alert">
            {{ session('error') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button>
        </div>
    @endif

    @if ($leaves->isEmpty())
        {{-- Use your custom alert info class --}}
        <div class="custom-alert custom-alert-info">
            You have not applied for any leave yet. <a href="{{ route('student.apply-leave') }}">Apply for one now?</a>
        </div>
    @else
        {{-- Use your custom table wrapper and table classes --}}
        <div class="custom-table-wrapper">
            <table class="custom-data-table">
                <thead> {{-- Your custom CSS styles th directly within .custom-data-table --}}
                    <tr>
                        <th>Sl No.</th>
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
                                {{-- Use your custom status badge classes --}}
                                <span class="status-badge
                                    @if($leave->overall_status === 'approved') status-approved
                                    @elseif($leave->overall_status === 'cancelled') status-cancelled
                                    @elseif(Str::startsWith($leave->overall_status, 'rejected_by_')) status-rejected
                                    @elseif(Str::startsWith($leave->overall_status, 'awaiting_')) status-pending
                                    @else status-default @endif">
                                    {{ Str::title(str_replace('_', ' ', $leave->overall_status)) }}
                                </span>
                                @if(Str::startsWith($leave->overall_status, 'awaiting_') && $leave->current_approver_role)
                                    <small class="d-block text-muted">Pending: {{ Str::title($leave->current_approver_role) }}</small>
                                @endif
                            </td>
                            <td>{{ $leave->created_at->format('d M Y, H:i') }}</td>
                            <td>{{ $leave->final_remarks ?: ($leave->remarks ?: 'N/A') }}</td>
                            <td class="actions-cell"> {{-- Added for potential specific styling of this cell --}}
                                @if(Str::startsWith($leave->overall_status, 'awaiting_') && $leave->overall_status !== 'cancelled')
                                    <form action="{{ route('student.cancel-leave', $leave->id) }}" method="POST" class="d-inline-form" onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                        @csrf
                                        {{-- Use your custom button classes --}}
                                        <button type="submit" class="custom-btn-sm custom-btn-warning">Cancel</button>
                                    </form>
                                @endif

                                @if($leave->overall_status === 'cancelled')
                                    <form action="{{ route('student.delete-leave', $leave->id) }}" method="POST" class="d-inline-form" onsubmit="return confirm('Are you sure you want to delete this cancelled leave record? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        {{-- Use your custom button classes --}}
                                        <button type="submit" class="custom-btn-sm custom-btn-danger">Delete</button>
                                    </form>
                                @endif

                                @if(!(Str::startsWith($leave->overall_status, 'awaiting_') && $leave->overall_status !== 'cancelled') && !($leave->overall_status === 'cancelled'))
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-center pagination-wrapper"> {{-- Added pagination-wrapper for your custom styles --}}
            {{ $leaves->links() }} {{-- You might need to publish and customize Laravel's pagination views to use your custom pagination CSS classes fully --}}
        </div>
    @endif
</div>
@endsection

@section('css')
<style>
    /* === Global Custom Styles (can be moved to a shared CSS file later) === */
    .page-section-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #2c3e50; /* Dark blue-gray */
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #ecf0f1; /* Light gray border */
    }

    .custom-btn, .custom-btn-sm {
        display: inline-block;
        font-weight: 400;
        text-align: center;
        vertical-align: middle;
        user-select: none;
        border: 1px solid transparent;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        text-decoration: none;
        transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
    .custom-btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
    /* Add more button colors as needed */
    .custom-btn-primary { color: #fff; background-color: #3498db; border-color: #3498db; }
    .custom-btn-primary:hover { background-color: #2980b9; border-color: #217dbb; }
    .custom-btn-info { color: #fff; background-color: #1abc9c; border-color: #1abc9c; }
    .custom-btn-info:hover { background-color: #16a085; border-color: #148f77; }
    .custom-btn-warning { color: #212529; background-color: #f39c12; border-color: #f39c12; }
    .custom-btn-warning:hover { background-color: #e08e0b; border-color: #d4830a; }
    .custom-btn-danger { color: #fff; background-color: #e74c3c; border-color: #e74c3c; }
    .custom-btn-danger:hover { background-color: #c0392b; border-color: #b33426; }


    .custom-alert {
        position: relative;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.25rem;
    }
    .custom-alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    .custom-alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
    .custom-alert-info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }
    .custom-alert-close { /* Adjusted to be more like a button if you want to style it explicitly */
        float: right;
        font-size: 1.2rem; /* Smaller than default Bootstrap */
        font-weight: 700;
        line-height: 1;
        color: inherit; /* Inherit color from parent alert */
        text-shadow: 0 1px 0 #fff;
        opacity: .5;
        background-color: transparent;
        border: 0;
        padding: 0; /* Remove padding if it's just an X */
        cursor: pointer;
    }
    .custom-alert-close:hover { opacity: .75; }

    .custom-table-wrapper {
        overflow-x: auto;
        background-color: #fff; /* White background for the table area */
        border: 1px solid #dfe3e8; /* Light grey border around the table */
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); /* Subtle shadow */
        margin-top: 1.5rem;
    }

    .custom-data-table {
        width: 100%;
        border-collapse: collapse; /* Ensures borders are neat */
    }
    .custom-data-table th,
    .custom-data-table td {
        padding: 12px 15px; /* More padding */
        text-align: left;
        border-bottom: 1px solid #dfe3e8; /* Light grey lines between rows */
        vertical-align: middle; /* Good for cells with varying content height */
    }
    .custom-data-table th {
        background-color: #f9fafb; /* Very light grey for header */
        font-weight: 600; /* Bolder header text */
        color: #374151; /* Darker grey for header text */
        text-transform: uppercase;
        font-size: 0.85em;
        letter-spacing: 0.05em;
    }
    /* Optional: Grid lines (if you want vertical borders too) */
    .custom-data-table th,
    .custom-data-table td {
        border-right: 1px solid #dfe3e8;
    }
    .custom-data-table th:first-child,
    .custom-data-table td:first-child {
        border-left: 1px solid #dfe3e8; /* Add left border to the first cell */
    }
     .custom-data-table tr:first-child th {
         border-top: 1px solid #dfe3e8; /* Add top border to header cells */
    }
    .custom-data-table th:last-child,
    .custom-data-table td:last-child {
        border-right: 0; /* No right border on the last cell of a row */
    }
     /* Rounded corners for the table (applied to specific cells) */
     .custom-data-table tr:first-child th:first-child {border-top-left-radius: 3px;}
     .custom-data-table tr:first-child th:last-child {border-top-right-radius: 3px;}
     .custom-data-table tr:last-child td:first-child {border-bottom-left-radius: 3px;}
     .custom-data-table tr:last-child td:last-child {border-bottom-right-radius: 3px; border-bottom:0;} /* Remove bottom border for last row last cell if table has wrapper border */


    /* Status Badge Styling */
    .status-badge {
        color: #fff; /* Default white text, overridden by specific statuses if needed */
        padding: 0.3em 0.7em;
        font-size: 0.8em;
        font-weight: 600;
        border-radius: 12px; /* Pill shape */
        text-transform: capitalize; /* e.g., 'awaiting_hod' becomes 'Awaiting Hod' in HTML, then 'Awaiting hod' here */
        display: inline-block; /* Ensures padding and background apply correctly */
    }
    .status-badge.status-approved { background-color: #2ecc71; } /* Green */
    .status-badge.status-cancelled { background-color: #95a5a6; } /* Grey */
    .status-badge.status-rejected { background-color: #e74c3c; } /* Red */
    .status-badge.status-pending { background-color: #f39c12; color: #2c3e50;} /* Orange/Yellow, dark text for contrast */
    .status-badge.status-default { background-color: #bdc3c7; color: #2c3e50;} /* Lighter Grey, dark text */


    .actions-cell .custom-btn-sm {
        margin-right: 5px;
    }
    .actions-cell .custom-btn-sm:last-child {
        margin-right: 0;
    }
    .d-inline-form {
        display: inline-block;
    }

    /* Bootstrap utility classes you might still want to use or redefine if necessary */
    .text-muted { color: #6c757d !important; } /* From Bootstrap, keep if useful */
    .mt-4 { margin-top: 1.5rem !important; }  /* From Bootstrap, keep if useful */

    /* Pagination - You'll likely need to publish and customize Laravel's pagination views
       or specifically target Bootstrap's pagination classes if you want these to apply.
       The .pagination-wrapper is a good start for containing them. */
    .pagination-wrapper .pagination {
        display: flex;
        padding-left: 0;
        list-style: none;
        border-radius: 0.25rem;
        justify-content: center; /* Center pagination */
    }
    .pagination-wrapper .page-item .page-link {
        position: relative;
        display: block;
        padding: 0.5rem 0.75rem;
        margin-left: -1px; /* Collapses borders */
        line-height: 1.25;
        color: #3498db; /* Primary color */
        background-color: #fff;
        border: 1px solid #dee2e6; /* Light grey border */
    }
    .pagination-wrapper .page-item:first-child .page-link {
        margin-left: 0;
        border-top-left-radius: 0.25rem;
        border-bottom-left-radius: 0.25rem;
    }
    .pagination-wrapper .page-item:last-child .page-link {
        border-top-right-radius: 0.25rem;
        border-bottom-right-radius: 0.25rem;
    }
    .pagination-wrapper .page-item.active .page-link {
        z-index: 1; /* Ensure active page is on top */
        color: #fff;
        background-color: #3498db; /* Primary color for active */
        border-color: #3498db;
    }
    .pagination-wrapper .page-item.disabled .page-link {
        color: #6c757d; /* Muted color for disabled */
        pointer-events: none;
        cursor: auto;
        background-color: #fff;
        border-color: #dee2e6;
    }
    /* Hover states */
    .pagination-wrapper .page-item:not(.active):not(.disabled) .page-link:hover {
        color: #2374ab;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

</style>
@stop