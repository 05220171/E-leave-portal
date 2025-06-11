@extends('layouts.app')

@section('title', 'Edit Department - Super Admin') {{-- Added a title for consistency --}}

@section('css') {{-- Using @section('css') if your layouts.app.blade.php yields it --}}
<style>
    /* Card-like styling for the form container */
    .form-container-card {
        background-color: #fff;
        padding: 1.5rem; /* Similar to your other card bodies */
        border-radius: 0.25rem; /* Bootstrap's default card radius */
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); /* shadow-sm */
        margin-top: 1.5rem; /* mt-3 or mt-4 */
    }

    .form-container-card .form-group {
        margin-bottom: 1rem; /* mb-3 */
    }

    .form-container-card label {
        font-weight: 600; /* fw-semibold */
        margin-bottom: 0.5rem;
    }

    /* Assuming .form-control is styled globally or by Bootstrap if you use it.
       If not, you'd add .form-control styles here too. */

    /* Custom button styling (similar to your .custom-btn and .custom-btn-primary) */
    .internal-styled-btn {
        display: inline-block;
        font-weight: 400;
        text-align: center;
        vertical-align: middle;
        user-select: none;
        border: 1px solid transparent;
        padding: 0.375rem 0.75rem; /* Default button padding */
        font-size: 1rem; /* Default button font size */
        line-height: 1.5;
        border-radius: 0.25rem;
        text-decoration: none;
        transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        cursor: pointer;
    }

    .internal-btn-primary {
        color: #fff;
        background-color: #0d6efd; /* Standard Bootstrap primary blue, or your #3498db */
        border-color: #0d6efd;
    }
    .internal-btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }

    /* To make the update button smaller like your other specific buttons */
    .update-department-button-specific-size {
        padding-left: 0.3rem !important;
        padding-right: 0.3rem !important;
        /* Inherits vertical padding from .internal-styled-btn and .custom-btn-sm if also applied */
        /* If you also want it to be .custom-btn-sm equivalent height: */
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }

    /* Footer styling */
    .form-footer {
        margin-top: 1.5rem; /* Add some space above the footer */
        padding-top: 1rem; /* Add some padding inside the footer */
        border-top: 1px solid #dee2e6; /* Separator line */
        text-align: right; /* Default to right alignment for the button */
    }

    /* If you were to add a cancel button and needed specific alignment like before: */
    /*
    .form-footer.d-flex { display: flex !important; }
    .form-footer.align-items-center { align-items: center !important; }
    .form-footer .ms-auto { margin-left: auto !important; }
    .form-footer .me-2 { margin-right: 0.5rem !important; }
    */
</style>
@endsection


@section('content')
<div class="container">
    {{-- Title outside the card --}}
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h1 class="page-section-title text-start mb-0">Edit Department</h1>
        {{-- Optional: Back to List button if needed --}}
        {{-- <a href="{{ route('superadmin.departments.index') }}" class="custom-btn-sm custom-btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Departments
        </a> --}}
    </div>

    <div class="form-container-card"> {{-- Apply card-like styling --}}
        <form method="POST" action="{{ route('superadmin.departments.update', $department->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Department Name</label> {{-- Added for attribute for accessibility --}}
                <input type="text" {{-- Added type="text" --}}
                       id="name"     {{-- Added id for label association --}}
                       name="name"
                       value="{{ old('name', $department->name) }}" {{-- Added old() for robust error handling --}}
                       class="form-control @error('name') is-invalid @enderror" {{-- Added error class handling --}}
                       required>
                @error('name')
                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- Footer for the button --}}
            <div class="form-footer">
                <button type="submit" class="internal-styled-btn internal-btn-primary update-department-button-specific-size">
                    <i class="fas fa-save me-1"></i> Update {{-- Added icon for consistency --}}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

{{-- Assuming your layouts.app.blade.php has @yield('js') for scripts --}}
@section('js')
{{-- If you add Bootstrap's JS validation, you'd need it here or globally --}}
{{-- Example if you were using the needs-validation pattern:
<script>
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation') // Or target by a more specific ID/class
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
--}}
@endsection