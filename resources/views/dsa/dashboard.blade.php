@extends('layouts.app')

@section('title', 'DSA Dashboard - Pending Leave Applications')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4 text-center">DSA Dashboard</h1>
    <p class="text-center text-muted mb-4">Pending Leave Applications for Your Review</p>

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
    @if(session('info'))
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
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Department</th>
                                <th>Leave Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Reason & Document</th>
                                <th>Applied On</th>
                                <th>Status</th>
                                <th style="min-width: 270px;">Actions</th> {{-- Increased width for more content --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaves as $index => $leave)
                                <tr>
                                    <td>{{ $leaves->firstItem() + $index }}</td>
                                    <td>{{ $leave->student->name ?? 'N/A' }}</td>
                                    <td>{{ $leave->student->department->name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($leave->type)
                                            {{ $leave->type->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
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
                                        {{-- Action buttons part (Approve and a simple Reject visual button) --}}
                                        <div class="action-buttons-initial mb-2">
                                            <form action="{{ route('dsa.approve', $leave->id) }}" method="POST" style="display:inline-block; margin-right: 5px;">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" title="Approve Leave">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            {{-- This Reject button is now just a visual cue, the real action is the form below --}}
                                            <button type="button" class="btn btn-danger btn-sm" disabled title="Reject (use form below)">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>

                                        {{-- Rejection form details - VISIBLE BY DEFAULT --}}
                                        <div id="rejectFormContainer-dsa-{{ $leave->id }}" class="mt-1" style="border: 1px solid #ddd; padding: 8px; background-color: #fdfdfd; border-radius: 4px;">
                                           <form action="{{ route('dsa.reject', $leave->id) }}" method="POST">
                                                @csrf
                                                <div class="mb-2">
                                                    <label for="remarks-dsa-{{ $leave->id }}" class="form-label visually-hidden">Reason for Rejection:</label>
                                                    <textarea name="remarks" id="remarks-dsa-{{ $leave->id }}" class="form-control form-control-sm" rows="2" placeholder="Reason for Rejection (Optional)"></textarea>
                                                </div>
                                                
                                                <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($leaves->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

{{-- No @push('scripts') needed for this version as there's no JS toggle for rejection form --}}