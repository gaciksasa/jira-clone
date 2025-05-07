@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>User Management</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Create User</a>
    </div>

    <div class="card">
        <div class="card-header">Users</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="{{ $user->is_active ? '' : 'table-danger' }}">
                                <td>{{ $user->id }}</td>
                                <td><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></td>
                                <td>
                                    @if($user->department)
                                        {{ $user->department->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge text-bg-secondary"> {{ $role->name }} </span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection