{{-- File: resources/views/student/leave-status.blade.php --}}
@extends('layouts.app')

@section('title', 'Approved Leave Records') {{-- Changed title --}}

@section('content')
<div class="container mt-4">
    <h2 class="page-title mb-4">Approved Leave Records & Certificates</h2> {{-- Changed title --}}

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Use the $approvedLeaves variable passed from the controller --}}
    @if ($approvedLeaves->isEmpty())
        <div class="alert alert-info">
            You have no approved leave records yet.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
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
                    {{-- Loop through $approvedLeaves --}}
                    @foreach ($approvedLeaves as $index => $leave)
                        <tr>
                            <td>{{ $approvedLeaves->firstItem() + $index }}</td>
                            <td>{{ $leave->type->name ?? 'N/A' }}</td>
                            <td>{{ $leave->start_date->format('d M Y') }}</td>
                            <td>{{ $leave->end_date->format('d M Y') }}</td>
                            <td>{{ $leave->number_of_days ?? 'N/A' }}</td>
                            <td>
                                {{-- Status will always be 'approved' here, but good to show it --}}
                                <span class="badge bg-success">
                                    {{ Str::title(str_replace('_', ' ', $leave->overall_status)) }}
                                </span>
                            </td>
                            <td>{{ $leave->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                {{-- Download Button for Approved Leaves --}}
                                <a href="{{ route('student.leave.download-certificate', $leave->id) }}" class="btn btn-sm btn-primary" title="Download Leave Record">
                                    <i class="fas fa-download"></i> Download Record
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $approvedLeaves->links() }}
        </div>
    @endif
</div>
@endsection