{{-- resources/views/superadmin/users/index.blade.php --}}

@extends('layouts.app')

@section('content_header')
    <h1 class="m-0 text-dark">Manage Users</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card"> {{-- Assuming 'card' provides a background and maybe padding/shadow from your layout or custom CSS --}}
                <div class="card-header flex justify-between items-center"> {{-- Using Flexbox for layout (Tailwind) --}}
                    <h3 class="card-title text-lg font-semibold">User List</h3> {{-- Basic text styling --}}
                    <div class="card-tools space-x-2"> {{-- Add space between buttons --}}
                        {{-- Link to Import Users Form - Styled with Tailwind --}}
                        <a href="{{ route('superadmin.users.importForm') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            Import Users
                        </a>
                        {{-- Link to Create User Form - Styled with Tailwind --}}
                        <a href="{{ route('superadmin.users.create') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                            Add New User
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0"> {{-- Removed default padding if card adds it, adjust if needed --}}
                    @if(session('success'))
                        {{-- Tailwind styled success alert --}}
                        <div class="m-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                            {{-- Optional close button: you'd need Alpine.js or similar for interaction --}}
                            {{-- <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                              <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                            </span> --}}
                        </div>
                    @endif
                    @if(session('error'))
                        {{-- Tailwind styled error alert --}}
                         <div class="m-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    {{-- Added overflow-x-auto for responsiveness on small screens --}}
                    <div class="overflow-x-auto">
                        {{-- *** START OF TAILWIND TABLE *** --}}
                        <table id="usersTable" class="min-w-full divide-y divide-gray-200 border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    {{-- Added Tailwind classes for header cells --}}
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                        ID
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                        Email
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                        Role
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                        Department
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                        Registered At
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"> {{-- No right border on last header --}}
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($users as $user)
                                    <tr class="hover:bg-gray-50"> {{-- Hover effect --}}
                                        {{-- Added Tailwind classes for data cells --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            {{ $user->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            {{ $user->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            {{ $user->email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            {{ Str::title($user->role) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200"> {{-- Slightly muted text for potentially empty data --}}
                                            {{ $user->department ? $user->department->name : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">
                                            {{ $user->created_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-1"> {{-- Actions column, add space between buttons --}}
                                            <a href="{{ route('superadmin.users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 inline-flex items-center px-2.5 py-1 border border-indigo-400 rounded text-xs hover:bg-indigo-50">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
                                                Edit
                                            </a>
                                            <form action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                {{-- Basic Tailwind button styling for delete --}}
                                                <button type="submit" class="text-red-600 hover:text-red-900 inline-flex items-center px-2.5 py-1 border border-red-400 rounded text-xs hover:bg-red-50" onclick="return confirm('Are you sure you want to delete this user?');">
                                                     <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        {{-- Apply Tailwind classes to the empty state cell --}}
                                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 border-t border-gray-200">
                                            No users found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                         {{-- *** END OF TAILWIND TABLE *** --}}
                    </div>
                </div>
                <!-- /.card-body -->
                @if ($users->hasPages()) {{-- Only show footer if pagination is needed --}}
                <div class="card-footer clearfix p-4 border-t border-gray-200">
                     {{-- You might need to publish and customize Laravel's default pagination views for Tailwind: --}}
                     {{-- Run: php artisan vendor:publish --tag=laravel-pagination --}}
                     {{-- Then edit the files in resources/views/vendor/pagination to use Tailwind classes --}}
                    {{ $users->links() }}
                </div>
                @endif
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

{{-- Optional JS remains commented out --}}
{{--
@section('js')
    <script> console.log('Hi!'); </script>
@stop
--}}