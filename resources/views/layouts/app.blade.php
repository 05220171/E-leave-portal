<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <title>{{ config('app.name', 'Dashboard') }} - @yield('title', 'Page')</title>

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Main Custom Stylesheet --}}
  <link rel="stylesheet" href="{{ asset('css/student.css') }}"> {{-- Ensure sidebar CSS is in here or linked separately --}}

  {{-- Font Awesome for icons --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  {{-- For page-specific styles --}}
  @yield('css')

</head>
<body>
  <div id="app" class="dashboard"> {{-- 'dashboard' class from your student.css for flex layout --}}
    @auth {{-- Ensure user is authenticated before trying to access role --}}
      @php $userRole = auth()->user()->role; @endphp {{-- Get role once for efficiency --}}

      {{-- Sidebar inclusion based on user role --}}
      @if($userRole == 'student')
        @include('student.sidebar')
      @elseif($userRole == 'hod')
        @include('hod.sidebar')
      @elseif($userRole == 'dsa')
        @include('dsa.sidebar')
      @elseif($userRole == 'sso')
        @include('sso.sidebar')
      @elseif($userRole == 'admin') {{-- Assuming 'admin' is the role for superadmin --}}
        @include('superadmin.sidebar') {{-- This will now include the dropdown sidebar --}}
      @endif
    @endauth

    <div class="main-content"> {{-- 'main-content' class from your student.css --}}
      @yield('content')
    </div>
  </div>

  {{-- Global Scripts (like jQuery, Bootstrap JS (if you add it), your main app.js) would go here --}}
  {{-- <script src="{{ asset('js/app.js') }}"></script> --}}

  {{-- Page-specific scripts --}}
  @yield('js')

  {{-- Sidebar Dropdown JavaScript --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
        var dropdownToggles = document.querySelectorAll('.sidebar .dropdown-toggle');

        dropdownToggles.forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                var parentLi = this.closest('.sidebar-dropdown');
                var submenu = parentLi.querySelector('.sidebar-submenu');

                if (parentLi.classList.contains('open')) {
                    submenu.style.display = 'none';
                    parentLi.classList.remove('open');
                } else {
                    // Optional: Close other open dropdowns
                    document.querySelectorAll('.sidebar-dropdown.open').forEach(function(openDropdown) {
                        if (openDropdown !== parentLi) {
                            openDropdown.classList.remove('open');
                            openDropdown.querySelector('.sidebar-submenu').style.display = 'none';
                        }
                    });
                    submenu.style.display = 'block';
                    parentLi.classList.add('open');
                }
            });
        });

        // Ensure currently active dropdown stays open on page load
        var activeDropdown = document.querySelector('.sidebar-dropdown.active.open');
        if (activeDropdown) {
            var submenu = activeDropdown.querySelector('.sidebar-submenu');
            if (submenu) {
                submenu.style.display = 'block';
            }
        }
    });
  </script>

</body>
</html>