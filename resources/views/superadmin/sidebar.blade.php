{{-- resources/views/superadmin/sidebar.blade.php --}}
<div class="sidebar">
    <div class="sidebar-header">
        <h3>Super Admin</h3>
    </div>
    <ul>
        <li class="{{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('superadmin.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>

        {{-- Manage Users Dropdown --}}
        @php
            // Routes that make the "Manage Users" dropdown parent active
            $manageUserDropdownRoutes = [
                'superadmin.users.index',       // All Users
                // 'superadmin.users.create',   // No longer makes the dropdown active
                'superadmin.users.edit',        // Edit User (implicitly part of management)
                // 'superadmin.users.importForm',// No longer makes the dropdown active
                'superadmin.users.students',    // Manage Students
                'superadmin.users.staff'        // Manage Staff
            ];
            $isManageUserDropdownActive = false;
            foreach ($manageUserDropdownRoutes as $route) {
                if (request()->routeIs($route)) {
                    $isManageUserDropdownActive = true;
                    break;
                }
            }
        @endphp
        <li class="sidebar-dropdown {{ $isManageUserDropdownActive ? 'open active' : '' }}">
            <span class="dropdown-toggle">
                <i class="fas fa-users-cog"></i> Manage Users <i class="fas fa-chevron-right"></i>
            </span>
            <ul class="sidebar-submenu" style="{{ $isManageUserDropdownActive ? 'display: block;' : 'display: none;' }}">
                <li class="{{ request()->routeIs('superadmin.users.students') ? 'active' : '' }}">
                    <a href="{{ route('superadmin.users.students') }}"><i class="fas fa-user-graduate"></i> Manage Students</a>
                </li>
                <li class="{{ request()->routeIs('superadmin.users.staff') ? 'active' : '' }}">
                    <a href="{{ route('superadmin.users.staff') }}"><i class="fas fa-user-tie"></i> Manage Staffs</a>
                </li>
                <li class="{{ request()->routeIs('superadmin.users.index') ? 'active' : '' }}">
                    <a href="{{ route('superadmin.users.index') }}"><i class="fas fa-users"></i> All Users</a>
                </li>
            </ul>
        </li>



        {{-- Other Top-level links --}}
        <li class="{{ request()->routeIs('superadmin.departments.*') ? 'active' : '' }}">
            <a href="{{ route('superadmin.departments.index') }}"><i class="fas fa-building"></i> Manage Departments</a>
        </li>
        <li class="{{ request()->routeIs('superadmin.leave-types.*') || request()->routeIs('superadmin.leave-types.workflows.*') ? 'active' : '' }}">
            <a href="{{ route('superadmin.leave-types.index') }}"><i class="fas fa-list-alt"></i> Manage Leave Types</a>
        </li>


        <li>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>
    </ul>
</div>