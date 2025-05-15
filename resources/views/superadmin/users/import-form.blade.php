@extends('layouts.app')

@section('title', 'Import Users') {{-- Optional: Set a page title if your layout supports it --}}

@push('styles') {{-- Or @section('css') if your layout uses that for page-specific CSS --}}
<style>
    .close-page-button {
        position: fixed; /* Or absolute if you want it relative to the container, but fixed is common for page-level close */
        top: 20px;
        right: 25px;
        font-size: 2rem; /* Adjust size of the 'X' */
        font-weight: bold;
        color: #6c757d; /* A muted color, adjust as needed */
        text-decoration: none;
        z-index: 1050; /* Ensure it's above other content if needed */
        line-height: 1; /* Helps with vertical alignment of the 'X' */
    }
    .close-page-button:hover {
        color: #343a40; /* Darker on hover */
        text-decoration: none;
    }
</style>
@endpush

@section('content_header') {{-- If your layout has a content header section for titles --}}
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                {{-- The H1 is now part of the main content block below --}}
            </div>
            <div class="col-sm-6">
                {{-- Cross sign button - positioned via CSS --}}
                <a href="#" onclick="window.history.back(); return false;" class="close-page-button" title="Close/Go Back" aria-label="Close">
                    × {{-- This is the HTML entity for the 'X' multiplication sign --}}
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container" style="position: relative; padding-top: 20px;"> {{-- Added padding-top if close button is absolute and relative to this --}}
        {{-- If not using content_header, the cross button can be placed here with absolute positioning relative to this container --}}
        {{-- Example if button is placed directly in content (adjust CSS to position: absolute; top: 10px; right: 15px;):
        <a href="#" onclick="window.history.back(); return false;" class="close-page-button" title="Close/Go Back" aria-label="Close" style="position: absolute; top: 10px; right: 15px;">
            ×
        </a>
        --}}

        <h1>Import Users from Excel</h1>

        @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mt-3">
                <strong>{{ session('error') }}</strong>
                @if(session('import_errors'))
                    <ul class="mt-2">
                        @foreach(session('import_errors') as $import_error)
                            <li>{{ $import_error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <form action="{{ route('superadmin.users.import') }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            <div class="form-group mb-3"> {{-- Added mb-3 for spacing --}}
                <label for="file" class="form-label"></label><br><br>
                <input type="file" class="form-control @error('file') is-invalid @enderror" name="file" id="file" required>
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                   
                </small>
            </div>
            <button type="submit" class="btn btn-success">Import Users</button><br><br>
            <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary ml-2">Cancel</a> {{-- Added Cancel button --}}
        </form>
    </div>
@endsection