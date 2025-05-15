{{-- resources/views/hod/student-history.blade.php --}}
@extends('layouts.app')

@section('title', 'Student Leave History')

@section('sidebar')
    @include('hod.sidebar')
@endsection

@section('content')
    <h1>Student Leave History ({{ Auth::user()->department->name ?? 'Your Department' }})</h1>

    {{-- Check if the collection passed from the controller is empty --}}
    @if($leavesHistory->isEmpty())
        <p>No leave history found for students in this department.</p>
    @else
        <table class="table table-bordered table-striped"> {{-- Added table classes --}}
            <thead>
                <tr>
                    <th>Student</th>
                    {{-- Matric No Header Removed --}}
                    <th>Dates</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Submitted On</th>
                    {{-- Add other relevant columns like 'Document' if needed --}}
                </tr>
            </thead>
            <tbody>
                @foreach ($leavesHistory as $leave)
                    <tr>
                        {{-- Access student details via the relationship --}}
                        <td>{{ $leave->student->name ?? 'N/A' }}</td>
                        {{-- Matric No Data Cell Removed --}}
                        <td>{{ $leave->start_date?->format('d M Y') }} - {{ $leave->end_date?->format('d M Y') }}</td> {{-- Added null safe operator --}}
                        <td>{{ Str::limit($leave->reason, 50) }}</td>
                        {{-- Format status for better readability --}}
                        <td><span class="badge bg-{{ strpos($leave->status, 'reject') !== false ? 'danger' : (strpos($leave->status, 'approve') !== false ? 'success' : 'warning') }}">{{ ucfirst(str_replace('_', ' ', $leave->status)) }}</span></td>
                        <td>{{ $leave->created_at?->format('d M Y H:i') }}</td>
                        {{-- Add link to document if exists --}}
                        {{-- <td>
                            @if($leave->document_path)
                                <a href="{{ asset('storage/' . $leave->document_path) }}" target="_blank">View Doc</a>
                            @else
                                N/A
                            @endif
                        </td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination Links - Make sure you used ->paginate() in the controller --}}
        <div class="d-flex justify-content-center">
            {{ $leavesHistory->links() }}
        </div>
    @endif

@endsection

@push('styles')
{{-- Optional: Add styles for badges if not using Bootstrap 5 --}}
<style>
    .badge { display: inline-block; padding: .35em .65em; font-size: .75em; font-weight: 700; line-height: 1; color: #fff; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: .25rem; }
    .bg-success { background-color: #198754 !important; }
    .bg-danger { background-color: #dc3545 !important; }
    .bg-warning { background-color: #ffc107 !important; color: #000 !important;} /* Warning often needs dark text */
    /* Add other colors as needed */
</style>
@endpush