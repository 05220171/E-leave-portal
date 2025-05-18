<!-- resources/views/sso/sidebar.blade.php -->
<div class="sidebar"> {{-- Changed from <nav> for layout consistency --}}
  <h2>SSO Panel</h2>
  <ul>
    {{-- Approve/Reject Leaves --}}
    <li class="{{ request()->routeIs('sso.dashboard') ? 'active' : '' }}">
      <a href="{{ route('sso.dashboard') }}">
        <i class="fas fa-check-circle fa-fw"></i> Leave Records
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
