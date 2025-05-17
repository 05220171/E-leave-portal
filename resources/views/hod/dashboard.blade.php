@extends('layouts.app') {{-- Ensure this layout is correctly set up --}}

@section('title', 'HOD Dashboard - Pending Leave Applications')

@section('content')
<div class="container mt-4"> {{-- Consistent container styling --}}
    <h1 class="mb-4 text-center">HOD Dashboard</h1>
    <p class="text-center text-muted mb-4">Pending Leave Applications for Your Department</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
     @if(session('info')) {{-- For placeholder messages from approve/reject --}}
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    @if($leaves->isEmpty())
        <div class="alert alert-info text-center shadow-sm">
            <i class="fas fa-info-circle me-2"></i> No leave requests currently awaiting your approval.
        </div>
    @else
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i> Pending Approvals</h5>
            </div>
            <div class="card-body p-0"> {{-- p-0 to make table flush with card --}}
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0"> {{-- mb-0 to remove bottom margin if card-body has p-0 --}}
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Leave Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Applied On</th>
                                <th>Status</th>
                                <th style="min-width: 220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaves as $index => $leave)
                                <tr>
                                    <td>{{ $index + 1 }}</td> {{-- If using pagination, use $leaves->firstItem() + $index --}}
                                    <td>{{ $leave->student->name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($leave->type)
                                            {{ $leave->type->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $leave->start_date->format('d M Y') }}
                                        <small class="text-muted">to</small>
                                        {{ $leave->end_date->format('d M Y') }}
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
                                        {{-- Display overall_status with dynamic badge --}}
                                        <span class="badge
                                            @if($leave->overall_status === 'approved') bg-success
                                            @elseif($leave->overall_status === 'cancelled') bg-secondary
                                            @elseif(Str::startsWith($leave->overall_status, 'rejected_by_')) bg-danger
                                            @elseif(Str::startsWith($leave->overall_status, 'awaiting_')) bg-warning text-dark
                                            @else bg-info text-dark @endif">
                                            {{ Str::title(str_replace('_', ' ', $leave->overall_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- Approve Form --}}
                                        <form action="{{ route('hod.approve-leave', $leave->id) }}" method="POST" style="display:inline-block; margin-bottom: 5px;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" title="Approve Leave">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>

                                        {{-- Reject Form - We will enhance this form when we do action processing --}}
                                        {{-- For now, the remarks textarea is here, but the controller just shows an info message --}}
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectLeaveModal-{{ $leave->id }}" title="Reject Leave">
                                            <i class="fas fa-times"></i> Reject
                                        </button>

                                        <!-- Reject Modal (one per leave request) -->
                                        <div class="modal fade" id="rejectLeaveModal-{{ $leave->id }}" tabindex="-1" aria-labelledby="rejectLeaveModalLabel-{{ $leave->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('hod.reject-leave', $leave->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="remarks-{{ $leave->id }}" class="form-label">Reason for Rejection:</label>
                                                                <textarea name="remarks" id="remarks-{{ $leave->id }}" class="form-control" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
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
            </div>
             @if(method_exists($leaves, 'links') && $leaves->hasPages()) {{-- Check if $leaves is paginated --}}
                <div class="card-footer d-flex justify-content-center">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

@push('styles')
    {{-- You might already have Bootstrap and Font Awesome in your layouts.app --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"> --}}
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> --}}
@endpush

@push('scripts')
    {{-- You might already have Bootstrap JS in your layouts.app --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> --}}
@endpush