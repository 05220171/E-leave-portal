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

    {{-- Manage Leave Types Link --}}
    <li class="{{ request()->routeIs('superadmin.leave-types.index') || request()->routeIs('superadmin.leave-types.create') || request()->routeIs('superadmin.leave-types.edit') ? 'active' : '' }}">
      <a href="{{ route('superadmin.leave-types.index') }}">Manage Leave Types</a>
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
  color: #ffffff; /* Or your active text color */
  background-color: #4e5d6c; /* Or your active background color */
}
.sidebar ul li a {
  color: #c8c8c8; /* Your default link color */
  text-decoration: none;
  display: block;
  padding: 8px 15px;
}
.sidebar ul li a:hover {
  background-color: #3a4652; /* Hover background color */
  color: #ffffff; /* Hover text color */
}
.sidebar h2 {
  padding: 15px;
  margin-bottom: 10px;
  border-bottom: 1px solid #444;
}
.sidebar ul {
  list-style-type: none;
  padding-left: 0;
}
.sidebar {
  background-color: #2c3e50; /* Example background for the sidebar */
  color: #ecf0f1; /* Example text color for the sidebar */
  /* Add other styling like width, height, etc. as needed */
}
--}}
