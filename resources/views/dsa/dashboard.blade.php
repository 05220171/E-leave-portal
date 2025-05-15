@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4 text-center text-primary">DSA Dashboard - Pending Leave Applications</h1>

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
            No pending leave applications requiring DSA action at the moment.
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Reason</th>
                                <th>Applied On</th>
                                <th>Actions</th>
                                {{-- REMOVED Remarks TH --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaves as $leave)
                                <tr>
                                    <td>{{ $leave->student->name ?? 'N/A (ID: ' . $leave->student_id . ')' }}</td>
                                    <td>{{ $leave->leave_type }}</td>
                                    <td>{{ $leave->start_date instanceof \Carbon\Carbon ? $leave->start_date->format('Y-m-d') : $leave->start_date }}</td>
                                    <td>{{ $leave->end_date instanceof \Carbon\Carbon ? $leave->end_date->format('Y-m-d') : $leave->end_date }}</td>
                                    <td>{{ Str::limit($leave->reason, 60) }}</td>
                                    <td>{{ $leave->created_at instanceof \Carbon\Carbon ? $leave->created_at->format('Y-m-d H:i') : $leave->created_at }}</td>
                                    <td>
                                        {{-- Approve Form --}}
                                        <form action="{{ route('dsa.approve', $leave->id) }}" method="POST" style="display:inline-block; margin-bottom: 5px;">
                                            @csrf
                                            {{-- REMOVED hidden remarks input --}}
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this leave?')" title="Approve Leave">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>

                                        {{-- Reject Form with Remarks Textarea --}}
                                        {{-- The two forms were on the same line, so making reject form display:block to stack its contents --}}
                                        <form action="{{ route('dsa.reject', $leave->id) }}" method="POST" style="display:block;">
                                            @csrf
                                            {{-- REMOVED hidden remarks input --}}
                                            <div class="mb-2">
                                                <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Reason for rejection (optional)"></textarea>
                                                @error('remarks')
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
    {{-- REMOVED attachRemarks JavaScript --}}
@endpush