@extends('layouts.app') {{-- Or your superadmin layout --}}

@section('title', 'Manage Programs')

{{-- If your custom styles are not global, copy the @section('css') from departments/index.blade.php here --}}
{{-- @section('css') <style> ... your table styles ... </style> @endsection --}}

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-section-title text-start">Manage Programs</h1>
                <div>
                    <a href="{{ route('superadmin.programs.create') }}" class="header-action-btn header-btn-primary"> {{-- Use your custom button classes --}}
                        <i class="fas fa-plus"></i> Add New Program
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="custom-alert custom-alert-success" role="alert"> {{-- Use your custom alert classes --}}
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

            @if($programs->isEmpty())
                <div class="department-list-card mt-3"> {{-- Or a generic list-card class --}}
                    <div class="department-item text-center text-muted">
                        No programs found.
                        <a href="{{ route('superadmin.programs.create') }}" class="fw-semibold text-decoration-none ms-2">Add a new program?</a>
                    </div>
                </div>
            @else
                <div class="department-list-card mt-3"> {{-- Or a generic list-card class --}}
                    {{-- Using the list-style from your "Manage Events" reference for departments --}}
                    {{-- Optional Header Row --}}
                    <div class="department-item fw-bold" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <span class="department-name" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; flex-basis:10%; text-align:center;">#</span>
                        <span class="department-name" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; flex-basis:15%;">Code</span>
                        <span class="department-name" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; flex-basis:35%;">Program Name</span>
                        <span class="department-name" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; flex-basis:20%;">Department</span>
                        <span class="department-actions" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; flex-basis:20%; text-align:right;">Actions</span>
                    </div>
                    @foreach($programs as $index => $program)
                        <div class="department-item">
                            <span class="department-name" style="flex-basis:10%; text-align:center;">{{ $programs->firstItem() + $index }}</span>
                            <span class="department-name" style="flex-basis:15%;">{{ $program->code }}</span>
                            <span class="department-name" style="flex-basis:35%;">{{ $program->name }}</span>
                            <span class="department-name" style="flex-basis:20%;">{{ $program->department->name ?? 'N/A' }}</span>
                            <div class="department-actions" style="flex-basis:20%; text-align:right;">
                                <a href="{{ route('superadmin.programs.edit', $program->code) }}" class="action-button edit"> {{-- Use program code for route --}}
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('superadmin.programs.destroy', $program->code) }}" method="POST" class="d-inline-block"> {{-- Use program code for route --}}
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-button delete"
                                            onclick="return confirm('Are you sure you want to delete program: \'{{ addslashes($program->name) }}\'? This action cannot be undone.');">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($programs->hasPages())
                    <div class="mt-4 d-flex justify-content-center pagination-wrapper"> {{-- Use your custom pagination class --}}
                        {{ $programs->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection