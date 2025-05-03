@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Projects</h1>
        <a href="{{ route('projects.create') }}" class="btn btn-primary">Create Project</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">Filter Projects</div>
        <div class="card-body">
            <form method="GET" action="{{ route('projects.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Project name or key..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label for="department" class="form-label">Department</label>
                    <select class="form-select" id="department" name="department">
                        <option value="">All Departments</option>
                        @foreach(\App\Models\Department::all() as $department)
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

    @if($projects->count() > 0)
        <div class="row">
            @foreach($projects as $project)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $project->key }}</h5>
                            <span class="badge bg-primary">{{ $project->tasks_count }} tasks</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $project->name }}</h5>
                            <p class="card-text text-muted">{{ Str::limit($project->description, 100) ?: 'No description provided' }}</p>
                            @if($project->department)
                                <div class="mb-2">
                                    <span class="badge bg-info">
                                        <i class="bi bi-building"></i> {{ $project->department->name }}
                                    </span>
                                </div>
                            @endif
                            <div class="mt-2">
                                <small class="text-muted">Lead: {{ $project->lead->name }}</small>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">View</a>
                                <a href="{{ route('projects.board', $project) }}" class="btn btn-outline-primary">Board</a>
                                <a href="{{ route('projects.sprints.index', $project) }}" class="btn btn-outline-primary">Sprints</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info text-center">
            <h4 class="alert-heading">No projects found!</h4>
            <p>
                {{ request('search') || request('department') ? 'No projects match your search criteria. Try adjusting your filters.' : 'Get started by creating your first project.' }}
            </p>
            @if(!request('search') && !request('department'))
                <a href="{{ route('projects.create') }}" class="btn btn-primary">Create Project</a>
            @endif
        </div>
    @endif
</div>
@endsection