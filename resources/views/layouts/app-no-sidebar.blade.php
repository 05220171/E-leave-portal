<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>@yield('title', 'Dashboard')</title> {{-- Added yield for page-specific title --}}
  <link rel="stylesheet" href="{{ asset('css/student.css') }}"> {{-- You might want a more general CSS file here or add specific ones --}}
  {{-- Add other common CSS/JS includes if necessary (e.g., Bootstrap, FontAwesome if used elsewhere) --}}
  @stack('styles') {{-- For page-specific styles --}}
</head>
<body>
  <div class="dashboard">
    {{-- THE SIDEBAR INCLUDE BLOCK IS REMOVED FROM HERE --}}
    {{-- @auth
      @if(auth()->user()->role == 'student')
        @include('student.sidebar')
      @elseif(auth()->user()->role == 'hod')
        @include('hod.sidebar')
      @elseif(auth()->user()->role == 'dsa')
        @include('dsa.sidebar')
      @elseif(auth()->user()->role == 'sso')
        @include('sso.sidebar')
      @elseif(auth()->user()->role == 'admin')
        @include('superadmin.sidebar')
      @endif
    @endauth --}}

    <div class="main-content">
      {{-- If your original layout had a header section for titles above content, add it here --}}
      {{-- For example, if you used @yield('content_header') in create-user.blade.php --}}
      @hasSection('content_header')
        <header class="content-header"> {{-- Add your own styling/structure for this --}}
            @yield('content_header')
        </header>
      @endif

      @yield('content')
    </div>
  </div>
  @stack('scripts') {{-- For page-specific scripts --}}
</body>
</html>