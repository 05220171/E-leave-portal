{{-- resources/views/superadmin/sidebar.blade.php --}}

<div class="sidebar">
  <h2>Super Admin Panel</h2>
  <ul>
    {{-- Dashboard Link --}}
    <li class="{{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
      <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    </li>

    {{-- Manage Users Link --}}
    <li class="{{ request()->routeIs('superadmin.users.index') ? 'active' : '' }}">
      <a href="{{ route('superadmin.users.index') }}">Manage Users</a>
    </li>

    {{-- Manage Departments Link --}}
    <li class="{{ request()->routeIs('superadmin.departments.index') ? 'active' : '' }}">
      <a href="{{ route('superadmin.departments.index') }}">Manage Departments</a>
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

{{-- Optional: Add some basic CSS in your superadmin.css for the active class --}}
{{-- Example CSS:
.sidebar ul li.active a {
  font-weight: bold;
  color: #ffffff;
  background-color: #4e5d6c;
}
--}}
