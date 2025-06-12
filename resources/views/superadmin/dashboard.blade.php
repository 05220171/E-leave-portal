@extends('layouts.app')

@section('title', 'Super Admin Dashboard') {{-- Optional: Set a page title --}}

@section('content')

{{-- CSS for Dashboard Cards - Place this in a @push('styles') section if your layout supports it,
     or in your main student.css file, or keep it here within <style> tags. --}}
<style>
.dashboard-grid .card.dashboard-card {
  height: 200px;
  display: flex;
  flex-direction: column;
  border-radius: 8px;
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  background-color: #fff;
  border: 1px solid #e0e0e0;
  position: relative; /* IMPORTANT FOR STRETCHED-LINK TO WORK */
  /* box-shadow: 0 1px 2px 0 rgba(60,64,67,.3), 0 1px 3px 1px rgba(60,64,67,.15); */ /* Alternative shadow */
}

.dashboard-card-img-placeholder {
  height: 100px;
  background-color: #7f8c8d; /* Default placeholder color */
  background-size: cover;
  background-position: center;
}

.dashboard-card .card-body {
  padding: 12px 16px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: flex-start; /* Aligns content (title, then text) to the start */
}

.dashboard-card .card-title {
  font-size: 1rem;
  font-weight: 500;
  color: #3c4043;
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
/* Ensure the link within card-title behaves visually as part of the title */
.dashboard-card .card-title a {
    color: inherit; /* Inherits color from .card-title */
    text-decoration: none; /* Removes underline */
}
.dashboard-card .card-title a:hover {
    /* Optional: add hover effect if desired, e.g., subtle underline or color change */
    /* text-decoration: underline; */
}


.dashboard-card .card-text {
  font-size: 0.8rem;
  color: #5f6368;
  margin-bottom: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.dashboard-card .card-text.description {
    white-space: normal;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: calc(0.8rem * 1.4 * 2); /* font-size * line-height * lines */
    margin-top: 4px;
}

/* Example patterns (simplified) */
.pattern-blue-check {
  background-color: #4285f4; /* Google Blue */
  background-image: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.1) 75%, transparent 75%, transparent);
  background-size: 30px 30px;
}
.pattern-orange-circles {
  background-color: #fbbc05; /* Google Yellow/Orange */
  background-image: radial-gradient(circle, rgba(255,255,255,0.15) 25%, transparent 25%);
  background-size: 40px 40px;
}
.pattern-teal-triangles {
  background-color: #34a853; /* Google Green */
   background-image:
    linear-gradient(30deg, rgba(255,255,255,0.1) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.1) 87.5%, rgba(255,255,255,0.1)),
    linear-gradient(150deg, rgba(255,255,255,0.1) 12%, transparent 12.5%, transparent 87%, rgba(255,255,255,0.1) 87.5%, rgba(255,255,255,0.1));
  background-size: 30px 50px;
}
</style>

<div class="container-fluid">
  <div class="row">
    {{-- Sidebar Column --}}
    {{-- If you have a sidebar for superadmin, include it here. Example: --}}
    {{-- <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse"> --}}
    {{--   @include('superadmin.sidebar') --}}
    {{-- </div> --}}

    {{-- Main Content Area --}}
    {{-- Adjust column class if sidebar is active (e.g., col-md-9 ms-sm-auto col-lg-10 px-md-4) --}}
    <div class="col-12 px-md-4">
      <h1 class="mt-4 fw-bold">Welcome to Super Admin Dashboard 👋</h1>

      <div class="row mt-4 dashboard-grid">

        {{-- Card 1: Manage Users --}}
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
          <div class="card shadow-sm dashboard-card">
            <div class="dashboard-card-img-placeholder pattern-blue-check"></div>
            <div class="card-body">
              <div>
                <h5 class="card-title">
                  <a href="{{ route('superadmin.users.index') }}" class="stretched-link" aria-label="Manage Users">
                    Manage Users
                  </a>
                </h5>
                <p class="card-text"></p>
              </div>
            </div>
          </div>
        </div>

        {{-- Card 2: Manage Departments --}}
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
          <div class="card shadow-sm dashboard-card">
            <div class="dashboard-card-img-placeholder pattern-orange-circles"></div>
            <div class="card-body">
              <div>
                <h5 class="card-title">
                  <a href="{{ route('superadmin.departments.index') }}" class="stretched-link" aria-label="Manage Departments">
                    Manage Departments
                  </a>
                </h5>
                <p class="card-text"></p>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
          <div class="card shadow-sm dashboard-card">
            {{-- You can use a different pattern class or image placeholder if you have one for programs --}}
            <div class="dashboard-card-img-placeholder pattern-blue-stripes"></div> {{-- Example: pattern-blue-stripes --}}
            <div class="card-body">
              <div>
                <h5 class="card-title">
                  <a href="{{ route('superadmin.programs.index') }}" class="stretched-link" aria-label="Manage Programs">
                    Manage Programs
                  </a>
                </h5>
                <p class="card-text"></p> {{-- Example card text --}}
              </div>
            </div>
          </div>
        </div>

        {{-- Card 3: Manage Leave Types --}}
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
          <div class="card shadow-sm dashboard-card">
            <div class="dashboard-card-img-placeholder pattern-teal-triangles"></div>
            <div class="card-body">
              <div>
                <h5 class="card-title">
                  <a href="{{ route('superadmin.leave-types.index') }}" class="stretched-link" aria-label="Manage Leave Types">
                    Manage Leave Types
                  </a>
                </h5>
                <p class="card-text"></p>
              </div>
            </div>
          </div>
        </div>

        {{-- "Import Users" card has been REMOVED. --}}

        {{-- Add more col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4 cards here as needed --}}
        {{-- Example for a new card:
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
          <div class="card shadow-sm dashboard-card">
            <div class="dashboard-card-img-placeholder" style="background-color: #6c757d;"></div> // Another pattern/color
            <div class="card-body">
              <div>
                <h5 class="card-title">
                  <a href="#" class="stretched-link" aria-label="Another Action"> // Update route and label
                    Another Action
                  </a>
                </h5>
                <p class="card-text">Brief description here</p>
              </div>
            </div>
          </div>
        </div>
        --}}

      </div> {{-- End of .row.dashboard-grid --}}
    </div> {{-- End of .col (Main Content) --}}
  </div> {{-- End of .row (Overall page structure) --}}
</div> {{-- End of .container-fluid --}}
@endsection