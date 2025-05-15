@extends('layouts.app') {{-- Or 'layouts.superadmin-no-sidebar' if you prefer that for this page --}}

@section('title', 'Create Department') {{-- Optional: if your layout supports it --}}

@section('content_header') {{-- Optional: if your layout has a header section --}}
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Create New Department</h1>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6"> {{-- Adjusted column width for a single input form --}}
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Department Details</h3>
                </div>

                {{-- Optional: Display all validation errors at the top --}}
                @if ($errors->any())
                    <div class="alert alert-danger m-3"> {{-- Added margin for aesthetics --}}
                        <h5 class="alert-heading">Please correct the error(s) below:</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('superadmin.departments.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group"> {{-- Ensured form-group wraps label and input --}}
                            <label for="name">Department Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="name" {{-- Added ID for label association --}}
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror" {{-- Added error class --}}
                                   value="{{ old('name') }}" {{-- Added old() helper --}}
                                   required
                                   autofocus> {{-- Added autofocus for better UX --}}
                            @error('name')
                                <span class="invalid-feedback" role="alert"> {{-- Error display block --}}
                                    <strong>{{ $message }}</strong> {{-- This will show "The name has already been taken." --}}
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Create Department</button>
                        <a href="{{ route('superadmin.departments.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection