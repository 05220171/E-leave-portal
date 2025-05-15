{{-- resources/views/student/sidebar.blade.php --}}

<div class="sidebar">
  <h2>Student Panel</h2>
  <ul>
    {{-- Apply Leave Link --}}
    {{-- Adds 'active' class if the current route's name is 'student.apply-leave' --}}
    <li class="{{ request()->routeIs('student.apply-leave') ? 'active' : '' }}">
      <a href="{{ route('student.apply-leave') }}">Apply Leave</a>
    </li>

    {{-- Leave History Link --}}
    {{-- Adds 'active' class if the current route's name is 'student.leave-history' --}}
    <li class="{{ request()->routeIs('student.leave-history') ? 'active' : '' }}">
      <a href="{{ route('student.leave-history') }}">Leave History</a>
    </li>

    {{-- Leave Status Link --}}
    {{-- Adds 'active' class if the current route's name is 'student.leave-status' --}}
    <li class="{{ request()->routeIs('student.leave-status') ? 'active' : '' }}">
      <a href="{{ route('student.leave-status') }}">Leave Status</a>
    </li>

    {{-- Optional: Logout Link --}}
    {{-- This assumes you have a named route 'logout' (standard with Laravel Breeze/Jetstream/Fortify) --}}
    <li>
      {{-- Use a form for logout as it should be a POST request for security --}}
      <form method="POST" action="{{ route('logout') }}" id="student-logout-form" style="display: none;">
        @csrf
      </form>
      {{-- This link triggers the form submission --}}
      <a href="{{ route('logout') }}"
         onclick="event.preventDefault(); document.getElementById('student-logout-form').submit();">
         Logout
      </a>
    </li>

  </ul>
</div>

{{-- Optional: Add some basic CSS in your student.css for the active class --}}
{{-- /* Example in student.css */
.sidebar ul li.active a {
  font-weight: bold;
  color: #ffffff; /* Example active color */
  background-color: #4e5d6c; /* Example active background */
}
*/ --}}