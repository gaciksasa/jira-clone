@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header h5">My Profile</div>

                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4>{{ Auth::user()->name }}</h4>
                        <div>
                            <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profile</a>
                            <a href="{{ route('profile.password') }}" class="btn btn-outline-primary">Change Password</a>
                            <a href="{{ route('vacation.index') }}" class="btn btn-outline-primary">My Calendar</a>
                        </div>
                    </div>
                    <div class="text-center mb-4">
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Profile Picture" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="avatar-placeholder rounded-circle bg-secondary d-flex justify-content-center align-items-center text-white" style="width: 150px; height: 150px; margin: 0 auto;">
                                <span style="font-size: 60px;">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div class="mt-2">
                            <a href="{{ route('profile.avatar') }}" class="btn btn-sm btn-outline-primary">Change Picture</a>
                        </div>
                    </div>

                    <dl class="row">
                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9">{{ Auth::user()->name }}</dd>

                        <dt class="col-sm-3">Department</dt>
                        <dd class="col-sm-9">
                            @if(Auth::user()->department)
                                {{ Auth::user()->department->name }}
                            @else
                                <span class="text-muted">Not assigned to any department</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ Auth::user()->email }}</dd>
                        
                        <dt class="col-sm-3">Joined</dt>
                        <dd class="col-sm-9">{{ Auth::user()->created_at->format('d.m.Y') }}</dd>
                        
                        <dt class="col-sm-3">Roles</dt>
                        <dd class="col-sm-9">
                            @forelse(Auth::user()->roles as $role)
                                <span class="badge bg-primary me-1">{{ ucfirst($role->name) }}</span>
                            @empty
                                <span class="text-muted">No roles assigned</span>
                            @endforelse
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection