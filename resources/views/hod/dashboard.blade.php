@extends('layouts.app') {{-- Ensure this layout has @stack('styles') and @stack('scripts') --}}

@section('title', 'HOD Dashboard - Leave Applications')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4 text-center text-primary">HOD Dashboard - Leave Applications</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($leaves->isEmpty())
        <div class="alert alert-info text-center">
            No leave requests found for your department requiring HOD action.
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Reason</th>
                                <th>Applied On</th>
                                <th>Status</th>
                                <th>Actions</th>
                                {{-- REMOVED Remarks TH --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaves as $index => $leave)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $leave->student->name ?? 'N/A (ID: ' . $leave->student_id . ')' }}</td>
                                    <td>{{ $leave->leave_type ?? 'N/A' }}</td>
                                    <td>{{ $leave->start_date instanceof \Carbon\Carbon ? $leave->start_date->format('Y-m-d') : \Carbon\Carbon::parse($leave->start_date)->format('Y-m-d') }}</td>
                                    <td>{{ $leave->end_date instanceof \Carbon\Carbon ? $leave->end_date->format('Y-m-d') : \Carbon\Carbon::parse($leave->end_date)->format('Y-m-d') }}</td>
                                    <td>{{ Str::limit($leave->reason, 60) }}</td>
                                    <td>{{ $leave->created_at instanceof \Carbon\Carbon ? $leave->created_at->format('Y-m-d H:i') : \Carbon\Carbon::parse($leave->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @php
                                            $status = strtolower(str_replace('_', ' ', $leave->status));
                                            $badgeClass = 'bg-secondary';
                                            if (str_contains($status, 'pending') || str_contains($status, 'awaiting')) $badgeClass = 'bg-warning text-dark'; // Adjusted for 'awaiting'
                                            elseif (str_contains($status, 'approved')) $badgeClass = 'bg-success';
                                            elseif (str_contains($status, 'rejected')) $badgeClass = 'bg-danger';
                                            elseif (str_contains($status, 'cancelled')) $badgeClass = 'bg-info text-dark';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucwords($status) }}</span>
                                    </td>
                                    <td>
                                        {{-- Approve Form --}}
                                        <form action="{{ route('hod.approve-leave', $leave->id) }}" method="POST" style="display:inline-block; margin-bottom: 5px;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this leave?')" title="Approve Leave">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>

                                        {{-- Reject Form with Remarks Textarea --}}
                                        <form action="{{ route('hod.reject-leave', $leave->id) }}" method="POST" style="display:block;"> {{-- Changed to display:block for stacking --}}
                                            @csrf
                                            <div class="mb-2"> {{-- Margin bottom for spacing --}}
                                                <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Reason for rejection (optional)"></textarea>
                                                @error('remarks') {{-- Display validation error for remarks related to this specific leave if form submits and fails --}}
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this leave?')" title="Reject Leave">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </td>
                                    {{-- REMOVED Remarks TD and its textarea --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    {{-- REMOVED attachHodRemarks JavaScript --}}
@endpush