<!-- resources/views/dsa/sidebar.blade.php -->
<div class="sidebar"> {{-- Changed from <nav> for layout consistency --}}

  <h2>DSA Panel</h2>
  <ul>
    {{-- Approve/Reject Leaves --}}
    <li class="{{ request()->routeIs('dsa.dashboard') ? 'active' : '' }}">
      <a href="{{ route('dsa.dashboard') }}">
        <i class="fas fa-check-circle fa-fw"></i> Approve / Reject Leave
      </a>
    </li>
    <li class="{{ request()->routeIs('dsa.approved-records') ? 'active' : '' }}">
      <a href="{{ route('dsa.approved-records') }}">
        <i class="fas fa-check-circle fa-fw"></i> Approved Records
      </a>
    </li>

    

    {{-- Logout Button --}}
    <li>
      <form method="POST" action="{{ route('logout') }}" id="hod-logout-form" style="display: none;">
        @csrf
      </form>
      <a href="{{ route('logout') }}"
         onclick="event.preventDefault(); document.getElementById('hod-logout-form').submit();">
         <i class="fas fa-sign-out-alt fa-fw"></i> Logout
      </a>
    </li>
  </ul>
</div>
