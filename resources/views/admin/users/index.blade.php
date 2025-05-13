@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Users Management</h2>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-plus me-1"></i> Create User</a>
    </div>

    <div class="card mb-4">
        <div class="card-header border-0 py-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search by name or email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="department_id" class="form-label">Department</label>
                    <select class="form-select" id="department_id" name="department_id">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="role_id" class="form-label">Role</label>
                    <select class="form-select" id="role_id" name="role_id">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Users</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route('admin.users.index', array_merge(request()->except(['sort_by', 'sort_direction']), [
                                    'sort_by' => 'id',
                                    'sort_direction' => ($sortField === 'id' && $sortDirection === 'asc') ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    ID
                                    @if($sortField === 'id')
                                        <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.users.index', array_merge(request()->except(['sort_by', 'sort_direction']), [
                                    'sort_by' => 'name',
                                    'sort_direction' => ($sortField === 'name' && $sortDirection === 'asc') ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Name
                                    @if($sortField === 'name')
                                        <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.users.index', array_merge(request()->except(['sort_by', 'sort_direction']), [
                                    'sort_by' => 'department',
                                    'sort_direction' => ($sortField === 'department' && $sortDirection === 'asc') ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Department
                                    @if($sortField === 'department')
                                        <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.users.index', array_merge(request()->except(['sort_by', 'sort_direction']), [
                                    'sort_by' => 'email',
                                    'sort_direction' => ($sortField === 'email' && $sortDirection === 'asc') ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Email
                                    @if($sortField === 'email')
                                        <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Roles</th>
                            <th>
                                <a href="{{ route('admin.users.index', array_merge(request()->except(['sort_by', 'sort_direction']), [
                                    'sort_by' => 'is_active',
                                    'sort_direction' => ($sortField === 'is_active' && $sortDirection === 'asc') ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Status
                                    @if($sortField === 'is_active')
                                        <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
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
                                        <span class="badge bg-info me-1">{{ $role->name }}</span>
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
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $users->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</div>
@endsection