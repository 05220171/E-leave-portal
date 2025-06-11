@extends('layouts.app')

@section('title', 'Create Department')

{{-- MODIFICATION 1: Add this new CSS section --}}
@section('css')
<style>
    /* 
     * THE FIX:
     * This rule specifically targets any link (<a>) that looks like a button
     * inside the card footer, and makes it visually identical to the
     * <button> element, which is styled by your student.css file.
    */
    .card-footer .btn {
        margin-top: 0; /* Override the margin-top from the student.css rule */
        background: #3498db;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none; /* Ensure links don't have underlines */
    }

    .card-footer .btn:hover {
        background: #2980b9;
    }

    /* Adjustments for the button that is a <button> tag to match */
    .card-footer button.btn {
        margin-top: 0;
    }
</style>
@endsection


@section('content_header')
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
        <div class="col-md-8 col-lg-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Department Details</h3>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger m-3">
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
                        <div class="form-group">
                            <label for="name">Department Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   required
                                   autofocus>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Create Department</button>
                        
                        {{-- MODIFICATION 2: Ensure the Cancel button has the "btn" class --}}
                        <a href="{{ route('superadmin.departments.index') }}" class="btn ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection