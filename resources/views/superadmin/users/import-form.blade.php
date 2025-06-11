{{-- resources/views/superadmin/users/import-form.blade.php --}}

@extends('layouts.app')

@section('title', 'Import Users')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                {{-- Title will be in the main content section --}}
            </div>
            <div class="col-sm-6 text-right"> {{-- Added text-right for alignment if needed --}}
                <a href="{{ route('superadmin.users.index') }}" class="close-page-button" title="Cancel and Go to Users List" aria-label="Close">
                    
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container"> {{-- Removed inline style, rely on global styles or CSS section --}}
        
        <h1 class="page-section-title">Import Users from Excel 📥</h1>

        @if(session('success'))
            <div class="custom-alert custom-alert-success mt-3">
                {{ session('success') }}
                <button type="button" class="custom-alert-close" onclick="this.parentElement.style.display='none'"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="custom-alert custom-alert-danger mt-3">
                <strong>{{ session('error') }}</strong>
                @if(session('import_errors'))
                    <ul class="mt-2 mb-0"> {{-- Added mb-0 to ul for better spacing within alert --}}
                        @foreach(session('import_errors') as $import_error)
                            <li>{{ $import_error }}</li>
                        @endforeach
                    </ul>
                @endif
                <button type="button" class="custom-alert-close" onclick="this.parentElement.style.display='none'"></button>
            </div>
        @endif

        <form action="{{ route('superadmin.users.import') }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            <div class="form-group mb-3">
                <label for="file" class="form-label">Select Excel File</label> {{-- Added more descriptive label --}}
                <input type="file" class="form-control @error('file') is-invalid @enderror" name="file" id="file" required accept=".xlsx, .xls, .csv"> {{-- Added accept attribute --}}
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                   
                   {{-- You can add a link to a template file here if you have one --}}
                   {{-- <a href="{{ asset('path/to/your/template.xlsx') }}" download>Download template</a> --}}
                </small>
            </div>
            
            <div class="actions-bar mt-4"> {{-- Grouping buttons --}}
                <button type="submit" class="custom-btn custom-btn-success mr-2">
                    <i class="fas fa-file-import"></i> Import 
                </button>
                <a href="{{ route('superadmin.users.index') }}" class="custom-btn custom-btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

@section('css')
<style>
    .page-section-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #ecf0f1;
    }

    .actions-bar {
        margin-bottom: 1.5rem; /* Consistent spacing */
    }

    .custom-btn, .custom-btn-sm { /* Combined from index.blade.php */
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
        text-decoration: none !important; /* Ensure no underline from a tags */
        transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
    .custom-btn i, .custom-btn-sm i { /* Icon spacing */
        margin-right: 0.35em;
    }

    /* Specific Button Colors (can add more from index.blade.php if needed) */
    .custom-btn-success { color: #fff; background-color: #2ecc71; border-color: #2ecc71; } /* Green for success/import */
    .custom-btn-success:hover { background-color: #27ae60; border-color: #27ae60; }
    .custom-btn-secondary { color: #fff; background-color: #95a5a6; border-color: #95a5a6; } /* Grey for cancel/secondary */
    .custom-btn-secondary:hover { background-color: #7f8c8d; border-color: #7f8c8d; }
    
    /* Primary and Info buttons from index.blade.php (if needed elsewhere) */
    .custom-btn-primary { color: #fff; background-color: #3498db; border-color: #3498db; }
    .custom-btn-primary:hover { background-color: #2980b9; border-color: #217dbb; }
    .custom-btn-info { color: #fff; background-color: #1abc9c; border-color: #1abc9c; }
    .custom-btn-info:hover { background-color: #16a085; border-color: #148f77; }


    /* Alerts from index.blade.php */
    .custom-alert {
        position: relative;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.25rem;
    }
    .custom-alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    .custom-alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
    .custom-alert-close { /* Shared close button style for alerts */
        float: right;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: inherit;
        text-shadow: 0 1px 0 #fff;
        opacity: .5;
        background-color: transparent;
        border: 0;
        cursor: pointer;
        padding: 0; /* Reset padding if any */
    }
    .custom-alert-close:hover { opacity: .75; }

    /* Close Page Button from your target import form */
    .close-page-button {
        /* position: fixed; Top right of viewport */
        /* For positioning relative to content_header or page, ensure parent has position:relative if using absolute */
        /* This example keeps it simple and relies on its placement in the header column */
        font-size: 2rem;
        font-weight: bold;
        color: #6c757d;
        text-decoration: none;
        line-height: 1;
        padding: 0.25rem 0.5rem; /* Add some padding for easier clicking */
        /* z-index: 1050; /* If needed */
    }
    .close-page-button:hover {
        color: #343a40;
        text-decoration: none;
    }

    /* Bootstrap form styling support (if not globally available or needs override) */
    .form-group { margin-bottom: 1rem; }
    .form-label { display: inline-block; margin-bottom: .5rem; font-weight: 500; }
    .form-control {
        display: block;
        width: 100%;
        padding: .375rem .75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
    .form-control:focus {
        color: #495057;
        background-color: #fff;
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 .2rem rgba(0,123,255,.25);
    }
    .is-invalid { border-color: #dc3545 !important; } /* Ensure override if needed */
    .invalid-feedback {
        display: none; /* Hidden by default */
        width: 100%;
        margin-top: .25rem;
        font-size: .875em;
        color: #dc3545;
    }
    .form-control.is-invalid ~ .invalid-feedback {
        display: block; /* Show when is-invalid is present */
    }
    .text-muted { color: #6c757d !important; }
    .mt-2 { margin-top: 0.5rem !important; }
    .mt-3 { margin-top: 1rem !important; }
    .mt-4 { margin-top: 1.5rem !important; }
    .mb-0 { margin-bottom: 0 !important; }
    .mb-3 { margin-bottom: 1rem !important; }
    .mr-2 { margin-right: 0.5rem !important; }
    .text-right { text-align: right !important; }
</style>
@stop

@section('js')
    <script>
        console.log('Import users page styled with custom theme loaded!');
        // Optional: Add any specific JS for this page, e.g., file input validation hints
        // document.getElementById('file').addEventListener('change', function(e){
        //     var fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
        //     // You could update a label or do other things here
        //     console.log('File selected: ' + fileName);
        // });
    </script>
@stop