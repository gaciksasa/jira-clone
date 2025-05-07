@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $project->name }}</h1>
            <p class="text-muted mb-0">{{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.board', $project) }}" class="btn btn-outline-primary">Board</a>
            <!--<a href="{{ route('projects.tasks.index', $project) }}" class="btn btn-outline-primary">Tasks</a>
            <a href="{{ route('projects.sprints.index', $project) }}" class="btn btn-outline-primary">Sprints</a>
            <a href="{{ route('projects.members.index', $project) }}" class="btn btn-outline-primary">Members</a>-->
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-primary">Edit</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header h5">Description</div>
                <div class="card-body">
                    {!! nl2br(e($project->description)) ?: '<em>No description provided</em>' !!}
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Recent Tasks</h5>
                    <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-primary">Create Task</a>
                </div>
                <div class="card-body">
                    @if($tasks->count() > 0)
                        <div class="list-group">
                            @foreach($tasks as $task)
                                <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="list-group-item list-group-item-action mb-2">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">{{ $task->task_number }}: {{ $task->title }}</h5>
                                        <small>{{ $task->updated_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge" style="background-color: {{ $task->status->color ?? '#6c757d' }}">
                                                {{ $task->status->name }}
                                            </span>
                                            <span class="badge" style="background-color: {{ $task->type->color ?? '#6c757d' }}">
                                                {{ $task->type->name }}
                                            </span>
                                            <span class="badge" style="background-color: {{ $task->priority->color ?? '#6c757d' }}">
                                                {{ $task->priority->name }}
                                            </span>
                                        </div>
                                        <small>{{ $task->assignee->name ?? 'Unassigned' }}</small>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center">No tasks in this project yet.</p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-primary">Create First Task</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header h5">Project Stats</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center border-end">
                            <h3>{{ $tasks->count() }}</h3>
                            <p class="text-muted">Total Tasks</p>
                        </div>
                        <div class="col-6 text-center">
                            <h3>{{ $tasks->where('task_status_id', $statuses->where('slug', 'done')->first()->id ?? 0)->count() }}</h3>
                            <p class="text-muted">Completed</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header h5">Project Members</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($project->members as $member)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $member->name }}
                                @if($member->id === $project->lead_id)
                                    <span class="badge bg-primary">Lead</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection