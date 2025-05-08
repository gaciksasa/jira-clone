@extends('layouts.app')

@section('title', 'My Projects')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Projects</h1>
        @can('manage projects')
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                Create Project
            </a>
        @endcan
    </div>

    <div class="card mb-4">
        <div class="card-header border-0">
            <form method="GET" action="{{ route('projects.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search project name or key..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="department" name="department">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }} ({{ $department->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div>
                        <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">My Projects List</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route('projects.index', array_merge(request()->except(['sort_by', 'sort_direction']), [
                                    'sort_by' => 'key',
                                    'sort_direction' => (request('sort_by') == 'key' && request('sort_direction') == 'asc') ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Key
                                    @if(request('sort_by') == 'key')
                                        <i class="bi bi-arrow-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('projects.index', array_merge(request()->except(['sort_by', 'sort_direction']), [
                                    'sort_by' => 'name',
                                    'sort_direction' => (request('sort_by') == 'name' && request('sort_direction') == 'asc') ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Name
                                    @if(request('sort_by') == 'name')
                                        <i class="bi bi-arrow-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('projects.index', array_merge(request()->except(['sort_by', 'sort_direction']), [
                                    'sort_by' => 'department',
                                    'sort_direction' => (request('sort_by') == 'department' && request('sort_direction') == 'asc') ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Department
                                    @if(request('sort_by') == 'department')
                                        <i class="bi bi-arrow-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('projects.index', array_merge(request()->except(['sort_by', 'sort_direction']), [
                                    'sort_by' => 'tasks_count',
                                    'sort_direction' => (request('sort_by') == 'tasks_count' && request('sort_direction') == 'asc') ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Tasks
                                    @if(request('sort_by') == 'tasks_count')
                                        <i class="bi bi-arrow-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('projects.index', array_merge(request()->except(['sort_by', 'sort_direction']), [
                                    'sort_by' => 'members_count',
                                    'sort_direction' => (request('sort_by') == 'members_count' && request('sort_direction') == 'asc') ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Members
                                    @if(request('sort_by') == 'members_count')
                                        <i class="bi bi-arrow-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('projects.index', array_merge(request()->except(['sort_by', 'sort_direction']), [
                                    'sort_by' => 'lead',
                                    'sort_direction' => (request('sort_by') == 'lead' && request('sort_direction') == 'asc') ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Lead
                                    @if(request('sort_by') == 'lead')
                                        <i class="bi bi-arrow-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td>{{ $project->key }}</td>
                                <td><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></td>
                                <td>
                                    @if($project->department)
                                        @can('manage users')
                                            <a href="{{ route('admin.departments.show', $project->department) }}">
                                                {{ $project->department->name }}
                                            </a>
                                        @else
                                            {{ $project->department->name }}
                                        @endcan
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $project->tasks_count }}</td>
                                <td>{{ $project->members_count }}</td>
                                <td>
                                    @can('manage users')
                                        <a href="{{ route('admin.users.show', $project->lead) }}">
                                            {{ $project->lead->name }}
                                        </a>
                                    @else
                                        {{ $project->lead->name }}
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No projects found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection