@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Role Management</h1>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Create Role</a>
    </div>

    <div class="card">
        <div class="card-header">Roles</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Permissions</th>
                            <th>Users</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->id }}</td>
                                <td>{{ $role->name }}</td>
                                <td>
                                    @foreach($role->permissions->take(3) as $permission)
                                        <span class="badge bg-info me-1">{{ $permission->name }}</span>
                                    @endforeach
                                    @if($role->permissions->count() > 3)
                                        <span class="badge bg-secondary">+{{ $role->permissions->count() - 3 }} more</span>
                                    @endif
                                </td>
                                <td>{{ $role->users->count() }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Are you sure you want to delete this role?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No roles found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection