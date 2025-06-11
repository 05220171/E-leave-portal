<!-- resources/views/student/sidebar.blade.php -->
<div class="sidebar">
  <h2>Student Panel</h2>
  <ul>
    {{-- Apply Leave Link --}}
    <li class="{{ request()->routeIs('student.apply-leave') ? 'active' : '' }}">
      <a href="{{ route('student.apply-leave') }}">
        <i class="fas fa-file-signature fa-fw"></i> Apply Leave
      </a>
    </li>

    {{-- Leave Status Link --}}
    <li class="{{ request()->routeIs('student.leave-status') ? 'active' : '' }}">
      <a href="{{ route('student.leave-status') }}">
        <i class="fas fa-hourglass-half fa-fw"></i> Leave Status
      </a>
    </li>
    
    {{-- Leave Records Link (assuming this was your third link) --}}
    <li class="{{ request()->routeIs('student.leave-history') ? 'active' : '' }}">
      <a href="{{ route('student.leave-history') }}">
        <i class="fas fa-history fa-fw"></i> Leave Records
      </a>
    </li>

    {{-- Logout Link --}}
    <li>
      <form method="POST" action="{{ route('logout') }}" id="student-logout-form" style="display: none;">
        @csrf
      </form>
      <a href="{{ route('logout') }}"
         onclick="event.preventDefault(); document.getElementById('student-logout-form').submit();">
         <i class="fas fa-sign-out-alt fa-fw"></i> Logout
      </a>
    </li>

  </ul>
</div>