@extends('layouts.app')

@section('title', 'Manage Projects')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Dashboard (Projects)</h1>
                <a href="{{ route('projects.create') }}" class="btn btn-primary">Create Project</a>
            </div>

            <div class="card mb-4">
                <div class="card-header h5">Filter Projects</div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.projects.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Project name or key..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }} ({{ $department->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="lead" class="form-label">Project Lead</label>
                            <select class="form-select" id="lead" name="lead">
                                <option value="">All Leads</option>
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}" {{ request('lead') == $lead->id ? 'selected' : '' }}>
                                        {{ $lead->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div>
                                <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                                <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Projects List</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Key</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Lead</th>
                                    <th>Tasks</th>
                                    <th>Members</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $project)
                                    <tr>
                                        <td>{{ $project->key }}</td>
                                        <td><a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a></td>
                                        <td>
                                            @if($project->department)
                                                <span class="badge bg-info">{{ $project->department->name }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $project->lead->name }}</td>
                                        <td>{{ $project->tasks_count }}</td>
                                        <td>{{ $project->members->count() }}</td>
                                        <td>{{ $project->created_at->format('d.m.Y') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Are you sure you want to delete this project? This action cannot be undone.');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No projects found</td>
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