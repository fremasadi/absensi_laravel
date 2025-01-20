@extends('layouts.sb-admin-2')

@section('title', 'Dashboard')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>
    <div class="row">
        <!-- Example Content -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">Welcome!</div>
                <div class="card-body">
                    You are logged in as {{ auth()->user()->role }}.
                </div>
            </div>
        </div>
    </div>
@endsection