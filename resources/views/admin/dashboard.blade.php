@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container">
    <h1 class="mb-4">Admin Dashboard</h1>
    
    <div class="row">
        <div class="col-md-3">
            @include('layouts.admin-nav')
        </div>
        
        <div class="col-md-9">
            <div class="row">

                <div class="col-md-3 mb-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Departments</h5>
                            <h2 class="mb-0">{{ $departmentCount }}</h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <h2 class="mb-0">{{ $userCount }}</h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Projects</h5>
                            <h2 class="mb-0">{{ $projectCount }}</h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Tasks</h5>
                            <h2 class="mb-0">{{ $taskCount }}</h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">Recent Activities</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentUsers as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>User registered</td>
                                        <td>{{ $user->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No recent activities</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection