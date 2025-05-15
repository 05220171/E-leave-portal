@extends('layouts.superadmin')

@section('content')
    <div class="container">
        <h2>Manage Users</h2>
        <!-- Add your user management content here -->
        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">Add New User</a>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Loop through users and display them -->
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a href="{{ route('superadmin.users.edit', $user->id) }}" class="btn btn-warning">Edit</a>
                            <form action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
