@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Import Users from Excel</h1>
        <form action="{{ route('superadmin.users.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="file">Choose File</label>
                <input type="file" class="form-control" name="file" required>
            </div>
            <button type="submit" class="btn btn-success">Import</button>
        </form>
    </div>
@endsection
