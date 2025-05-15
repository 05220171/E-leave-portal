@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    {{-- Sidebar --}}
    {{-- <div class="col-md-3 col-lg-2 ..."> @include('layouts.superadmin-sidebar') </div> --}}

    {{-- Main Content --}}
    <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <h1 class="mt-4 fw-bold">Super Admin Dashboard</h1>

      <div class="row mt-4">
        
        {{-- Card 1: Manage Users (Duller Teal/Blue) --}}
        <div class="col-md-3 mb-4"> 
          <div class="card h-100 shadow-sm text-center position-relative" 
               style="min-height: 160px; /* Reduced height */
                      background: linear-gradient(45deg, #5f9ea0, #4682b4); /* CadetBlue to SteelBlue - duller */
                      color: white;
                      border-radius: 0.5rem; /* Slightly more rounded corners */">
            <div class="card-body d-flex flex-column justify-content-center align-items-center p-3"> 
              <div>
                <h5 class="card-title fw-bold" style="font-size: 1.5rem;">Manage Users</h5> 
                <p class="card-text mb-2" style="color: rgba(255,255,255,0.80); font-size: 1rem;">Add, edit, or remove user accounts and roles.</p>
              </div>
              <a href="{{ route('superadmin.users.index') }}" 
                 class="btn stretched-link mt-2" 
                 style="background-color: rgba(255, 255, 255, 0.1); 
                        border: 1px solid rgba(255, 255, 255, 0.25); 
                        color: white;
                        padding: 0.3rem 0.6rem; 
                        font-size: 0.85rem;">
                Manage Users
              </a>
            </div>
          </div>
        </div>

        {{-- Card 2: Manage Departments --}}
        <div class="col-md-3 mb-4"> 
          <div class="card h-100 shadow-sm text-center position-relative" 
               style="min-height: 160px; /* Consistent reduced height */
                      background: linear-gradient(45deg, #4682b4, #708090); /* SteelBlue to SlateGray - duller */
                      color: white;
                      border-radius: 0.5rem;">
            <div class="card-body d-flex flex-column justify-content-center align-items-center p-3">
              <div>
                <h5 class="card-title fw-bold" style="font-size: 1.5rem;">Manage Departments</h5>
                <p class="card-text mb-2" style="color: rgba(255,255,255,0.80); font-size: 1rem;">View and organize department details.</p>
              </div>
              <a href="{{ route('superadmin.departments.index') }}" 
                 class="btn stretched-link mt-2" 
                 style="background-color: rgba(255,255,255,0.1); 
                        border: 1px solid rgba(255,255,255,0.25); 
                        color: white;
                        padding: 0.3rem 0.6rem; 
                        font-size: 0.85rem;">
                View Departments
              </a>
            </div>
          </div>
        </div>

        {{-- Example: Import Users Card --}}
        {{-- <div class="col-md-3 mb-4">
          <div class="card h-100 shadow-sm text-center position-relative" 
               style="min-height: 160px;
                      background: linear-gradient(45deg, #607d8b, #556b2f); // BlueGrey to DarkOliveGreen - duller
                      color: white;
                      border-radius: 0.5rem;">
            <div class="card-body d-flex flex-column justify-content-center align-items-center p-3">
              <div>
                <h5 class="card-title fw-bold" style="font-size: 1.1rem;">Import Users</h5>
                <p class="card-text mb-2" style="color: rgba(255,255,255,0.80); font-size: 0.85rem;">Bulk import users from a file.</p>
              </div>
              <a href="{{ route('superadmin.users.importForm') }}"
                 class="btn stretched-link mt-2"
                 style="background-color: rgba(255,255,255,0.1); 
                        border: 1px solid rgba(42, 214, 183, 0.25); 
                        color: white;
                        padding: 0.3rem 0.6rem; 
                        font-size: 0.85rem;">
                Go to Import
              </a>
            </div>
          </div>
        </div> --}}

        {{-- Add more col-md-3 cards here to fill the row if desired --}}

      </div> {{-- End of .row for cards --}}
    </div> {{-- End of .col (Main Content) --}}
  </div> {{-- End of .row (Page structure) --}}
</div> {{-- End of .container-fluid --}}
@endsection