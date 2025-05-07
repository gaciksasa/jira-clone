@extends('layouts.app')

@section('title', 'Role Details')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Role Details</h1>
        <div class="btn-group">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-primary">Back to Roles</a>
            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-secondary">Edit</a>
            @if($role->name !== 'admin')
                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Are you sure you want to delete this role?');" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Role Information</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">ID:</dt>
                        <dd class="col-sm-9">{{ $role->id }}</dd>
                        
                        <dt class="col-sm-3">Name:</dt>
                        <dd class="col-sm-9">{{ $role->name }}</dd>
                        
                        <dt class="col-sm-3">Guard:</dt>
                        <dd class="col-sm-9">{{ $role->guard_name }}</dd>
                        
                        <dt class="col-sm-3">Created:</dt>
                        <dd class="col-sm-9">{{ $role->created_at->format('d.m.Y H:i:s') }}</dd>
                        
                        <dt class="col-sm-3">Updated:</dt>
                        <dd class="col-sm-9">{{ $role->updated_at->format('d.m.Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">Permissions</div>
                <div class="card-body">
                    @if($role->permissions->count() > 0)
                        <div>
                            @foreach($role->permissions as $permission)
                                <span class="badge bg-info me-1 mb-1">{{ $permission->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <p>This role has no permissions assigned.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Users with this Role</div>
                <div class="card-body">
                    @if($usersWithRole->count() > 0)
                        <div class="list-group">
                            @foreach($usersWithRole as $user)
                                <a href="{{ route('admin.users.show', $user) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    {{ $user->name }}
                                    <span class="text-muted">{{ $user->email }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p>No users have this role assigned.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection