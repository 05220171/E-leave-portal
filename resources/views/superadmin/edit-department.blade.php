@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Department</h1>

    <form method="POST" action="{{ route('superadmin.departments.update', $department->id) }}">
        @csrf @method('PUT')
        <div class="form-group"><label>Department Name</label><input name="name" value="{{ $department->name }}" class="form-control" required></div>
        <button class="btn btn-primary mt-3">Update</button>
    </form>
</div>
@endsection
