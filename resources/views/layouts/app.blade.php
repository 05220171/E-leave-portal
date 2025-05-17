<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  {{-- Load Vite assets FIRST if they include Bootstrap --}}
  
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Load custom styles AFTER Bootstrap/main app CSS --}}
  {{-- Consider importing student.css within app.css instead --}}
  <link rel="stylesheet" href="{{ asset('css/student.css') }}">
</head>
<body>
  <div class="dashboard">
    @auth
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
    @endauth

    <div class="main-content">
      @yield('content')
    </div>
  </div>
</body>
</html>
