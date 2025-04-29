@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h1 class="mb-4">Dashboard</h1>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Your Projects</span>
                            <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary">New Project</a>
                        </div>
                        <div class="card-body">
                            @if($projects->count() > 0)
                                <div class="list-group">
                                    @foreach($projects as $project)
                                        <a href="{{ route('projects.show', $project) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1">{{ $project->name }}</h5>
                                                <p class="mb-1 text-muted">{{ $project->key }}</p>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">{{ $project->tasks_count }} tasks</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-center">You don't have any projects yet.</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <span>Assigned Tasks</span>
                        </div>
                        <div class="card-body">
                            @if($assignedTasks->count() > 0)
                                <div class="list-group">
                                    @foreach($assignedTasks as $task)
                                        <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1">{{ $task->task_number }}: {{ $task->title }}</h5>
                                                <small>{{ $task->project->key }}</small>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="badge" style="background-color: {{ $task->status->color ?? '#6c757d' }}">
                                                        {{ $task->status->name }}
                                                    </span>
                                                    <span class="badge" style="background-color: {{ $task->priority->color ?? '#6c757d' }}">
                                                        {{ $task->priority->name }}
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-center">You don't have any assigned tasks.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection