@extends('layouts.app') {{-- Or your main app layout --}}

@section('content_header')
    <h1 class="m-0 text-dark">DAA Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="mb-0">Hello, {{ $user->name }}! Welcome to the DAA Dashboard.</p>
                    {{-- DAA specific content will go here --}}
                </div>
            </div>
        </div>
    </div>
@stop