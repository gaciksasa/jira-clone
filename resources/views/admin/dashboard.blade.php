@extends('layouts.app')

@section('title', 'Dashboard Home')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Dashboard (Home)</h1>
            
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Users</h5>
                            <h2 class="mb-0">{{ $userCount }}</h2>
                            <a href="{{ route('admin.users.index') }}" class="text-white stretched-link"></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Projects</h5>
                            <h2 class="mb-0">{{ $projectCount }}</h2>
                            <a href="{{ route('admin.projects.index') }}" class="text-white stretched-link"></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Tasks</h5>
                            <h2 class="mb-0">{{ $taskCount }}</h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Departments</h5>
                            <h2 class="mb-0">{{ $departmentCount }}</h2>
                            <a href="{{ route('admin.departments.index') }}" class="text-white stretched-link"></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header admin-header">
                            <h5 class="mb-0">Recent Users</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentUsers as $user)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.users.show', $user) }}">
                                                        {{ $user->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->created_at->diffForHumans() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">No recent users</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header admin-header">
                            <h5 class="mb-0">System Health</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    PHP Version
                                    <span class="badge bg-primary rounded-pill">{{ PHP_VERSION }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Laravel Version
                                    <span class="badge bg-primary rounded-pill">{{ app()->version() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Environment
                                    <span class="badge bg-primary rounded-pill">{{ config('app.env') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Debug Mode
                                    <span class="badge bg-{{ config('app.debug') ? 'warning' : 'success' }} rounded-pill">
                                        {{ config('app.debug') ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection