@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Projects</h1>
        <a href="{{ route('projects.create') }}" class="btn btn-primary">Create Project</a>
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
                            <p class="card-text text-muted">{{ Str::limit($project->description, 100) }}</p>
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
        <div class="text-center">
            <p>No projects found. Get started by creating your first project.</p>
            <a href="{{ route('projects.create') }}" class="btn btn-primary">Create Project</a>
        </div>
    @endif
</div>
@endsection