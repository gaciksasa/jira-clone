@extends('layouts.app')

@section('title', $task->task_number . ' - ' . $task->title)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $task->task_number }}: {{ $task->title }}</h1>
            <p class="text-muted mb-0">{{ $project->name }} | {{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Project</a>
            <a href="{{ route('projects.board', $project) }}" class="btn btn-outline-primary">Board</a>
            <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">Description</div>
                <div class="card-body">
                    {!! nl2br(e($task->description)) ?: '<em>No description provided</em>' !!}
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Comments</span>
                </div>
                <div class="card-body">
                    @if($task->comments->count() > 0)
                        @foreach($task->comments as $comment)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>{{ $comment->user->name }}</strong>
                                    </div>
                                    <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                                <div>
                                    {!! nl2br(e($comment->content)) !!}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-center">No comments yet.</p>
                    @endif
                    
                    <form method="POST" action="{{ route('projects.tasks.comments.store', [$project, $task]) }}" class="mt-4">
                        @csrf
                        <div class="mb-3">
                            <label for="content" class="form-label">Add Comment</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="3" required></textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">Add Comment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">Details</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Type:</span>
                            <span class="badge" style="background-color: {{ $task->type->color ?? '#6c757d' }}">
                                {{ $task->type->name }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Status:</span>
                            <span class="badge" style="background-color: {{ $task->status->color ?? '#6c757d' }}">
                                {{ $task->status->name }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Priority:</span>
                            <span class="badge" style="background-color: {{ $task->priority->color ?? '#6c757d' }}">
                                {{ $task->priority->name }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Reporter:</span>
                            <span>{{ $task->reporter->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Assignee:</span>
                            <span>{{ $task->assignee->name ?? 'Unassigned' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Sprint:</span>
                            <span>{{ $task->sprint->name ?? 'Backlog' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Story Points:</span>
                            <span>{{ $task->story_points ?? 'Not specified' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Created:</span>
                            <span>{{ $task->created_at->format('M d, Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Updated:</span>
                            <span>{{ $task->updated_at->format('M d, Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            @if($task->labels->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">Labels</div>
                    <div class="card-body">
                        @foreach($task->labels as $label)
                            <span class="badge bg-secondary mb-1">{{ $label->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <div class="card">
                <div class="card-header">Actions</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="btn btn-primary">Edit Task</a>
                        
                        <form method="POST" action="{{ route('projects.tasks.destroy', [$project, $task]) }}" onsubmit="return confirm('Are you sure you want to delete this task? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">Delete Task</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection