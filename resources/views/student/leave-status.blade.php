{{-- File: resources/views/student/leave-status.blade.php --}}
@extends('layouts.app')

@section('title', 'Approved Leave Records')

@section('content')
<div class="container mt-4"> {{-- Bootstrap container is fine, or use your own custom container if defined --}}
    {{-- Use your custom page title class --}}
    <h2 class="page-section-title">Approved Leave Records & Certificates</h2>

    @if (session('success'))
        {{-- Use your custom alert classes --}}
        <div class="custom-alert custom-alert-success" role="alert">
            {{ session('success') }}
            {{-- Assuming Bootstrap's JS handles dismissal, or style your own custom-alert-close functionality --}}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button>
        </div>
    @endif
    @if (session('error'))
        {{-- Use your custom alert classes --}}
        <div class="custom-alert custom-alert-danger" role="alert">
            {{ session('error') }}
            <button type="button" class="custom-alert-close" data-bs-dismiss="alert" aria-label="Close">×</button>
        </div>
    @endif

    @if ($approvedLeaves->isEmpty())
        {{-- Use your custom alert info class --}}
        <div class="custom-alert custom-alert-info">
            You have no approved leave records yet.
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
                        <th>Status</th>
                        <th>Applied On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($approvedLeaves as $index => $leave)
                        <tr>
                            <td>{{ $approvedLeaves->firstItem() + $index }}</td>
                            <td>{{ $leave->type->name ?? 'N/A' }}</td>
                            <td>{{ $leave->start_date->format('d M Y') }}</td>
                            <td>{{ $leave->end_date->format('d M Y') }}</td>
                            <td>{{ $leave->number_of_days ?? 'N/A' }}</td>
                            <td>
                                {{-- Use your custom status badge class for approved status --}}
                                <span class="status-badge status-approved">
                                    {{ Str::title(str_replace('_', ' ', $leave->overall_status)) }}
                                </span>
                            </td>
                            <td>{{ $leave->created_at->format('d M Y, H:i') }}</td>
                            <td class="actions-cell"> {{-- Added for consistency with previous example --}}
                                {{-- Use your custom button classes --}}
                                <a href="{{ route('student.leave.download-certificate', $leave->id) }}" class="custom-btn-sm custom-btn-primary" title="Download Leave Record">
                                    <i class="fas fa-download"></i> Download Record {{-- Make sure Font Awesome is loaded if you use fas fa-download --}}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Add pagination-wrapper for your custom styles --}}
        <div class="mt-4 d-flex justify-content-center pagination-wrapper">
            {{ $approvedLeaves->links() }} {{-- Ensure your pagination views are styled or customize them --}}
        </div>
    @endif
</div>
@endsection

@section('css')
{{-- This CSS should ideally be in a global stylesheet linked in layouts.app.blade.php --}}
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
    .custom-alert-close {
        float: right;
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1;
        color: inherit;
        text-shadow: 0 1px 0 #fff;
        opacity: .5;
        background-color: transparent;
        border: 0;
        padding: 0;
        cursor: pointer;
    }
    .custom-alert-close:hover { opacity: .75; }

    .custom-table-wrapper {
        overflow-x: auto;
        background-color: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-top: 1.5rem;
    }

    .custom-data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .custom-data-table th,
    .custom-data-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #dfe3e8;
        vertical-align: middle;
    }
    .custom-data-table th {
        background-color: #f9fafb;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        font-size: 0.85em;
        letter-spacing: 0.05em;
    }
    .custom-data-table th,
    .custom-data-table td {
        border-right: 1px solid #dfe3e8;
    }
    .custom-data-table th:first-child,
    .custom-data-table td:first-child {
        border-left: 1px solid #dfe3e8;
    }
     .custom-data-table tr:first-child th {
         border-top: 1px solid #dfe3e8;
    }
    .custom-data-table th:last-child,
    .custom-data-table td:last-child {
        border-right: 0;
    }
     .custom-data-table tr:first-child th:first-child {border-top-left-radius: 3px;}
     .custom-data-table tr:first-child th:last-child {border-top-right-radius: 3px;}
     .custom-data-table tr:last-child td:first-child {border-bottom-left-radius: 3px;}
     .custom-data-table tr:last-child td:last-child {border-bottom-right-radius: 3px; border-bottom:0;}


    .status-badge {
        color: #fff;
        padding: 0.3em 0.7em;
        font-size: 0.8em;
        font-weight: 600;
        border-radius: 12px;
        text-transform: capitalize;
        display: inline-block;
    }
    .status-badge.status-approved { background-color: #2ecc71; } /* Green */
    .status-badge.status-cancelled { background-color: #95a5a6; } /* Grey */
    .status-badge.status-rejected { background-color: #e74c3c; } /* Red */
    .status-badge.status-pending { background-color: #f39c12; color: #2c3e50;} /* Orange/Yellow, dark text */
    .status-badge.status-default { background-color: #bdc3c7; color: #2c3e50;} /* Lighter Grey, dark text */


    .actions-cell .custom-btn-sm {
        margin-right: 5px;
    }
    .actions-cell .custom-btn-sm:last-child {
        margin-right: 0;
    }

    .pagination-wrapper .pagination {
        display: flex;
        padding-left: 0;
        list-style: none;
        border-radius: 0.25rem;
        justify-content: center;
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

    /* Ensure these utility classes are available or redefined if you don't use Bootstrap's */
    .mt-4 { margin-top: 1.5rem !important; }
    .d-flex { display: flex !important; }
    .justify-content-center { justify-content: center !important; }
</style>
@stop