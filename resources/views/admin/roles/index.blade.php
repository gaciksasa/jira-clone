@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Role Management</h2>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Create Role</a>
    </div>

    <div class="card">
        <div class="card-header">Roles</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Permissions</th>
                            <th>Users</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td><a href="{{ route('admin.roles.show', $role) }}">{{ $role->name }}</a></td>
                                <td>
                                    @foreach($role->permissions->take(5) as $permission)
                                        <span class="badge bg-info me-1">{{ $permission->name }}</span>
                                    @endforeach
                                    @if($role->permissions->count() > 5)
                                        <span class="badge bg-secondary">+{{ $role->permissions->count() - 5 }} more</span>
                                    @endif
                                </td>
                                <td>{{ $role->users->count() }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Are you sure you want to delete this role?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
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