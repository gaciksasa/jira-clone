@extends('layouts.app')

@section('title', 'Tasks with Label: ' . $label->name . ' - ' . $project->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Tasks with Label: <span class="badge py-1 px-3" style="background-color: {{ $label->color }}">{{ $label->name }}</span></h2>
            <p class="text-muted mb-0">{{ $project->name }} | {{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Project</a>
            <a href="{{ route('projects.tasks.index', $project) }}" class="btn btn-outline-primary">Tasks</a>
            <a href="{{ route('projects.labels.index', $project) }}" class="btn btn-outline-primary">Manage Labels</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Tasks</h5>
            <span class="badge bg-primary">{{ $tasks->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                @if($tasks->count() > 0)
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Assignee</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                                <tr>
                                    <td>{{ $task->task_number }}</td>
                                    <td>
                                        <a href="{{ route('projects.tasks.show', [$project, $task]) }}">
                                            {{ $task->title }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $task->type->color ?? '#6c757d' }}">
                                            {{ $task->type->name }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $task->status->color ?? '#6c757d' }}">
                                            {{ $task->status->name }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $task->priority->color ?? '#6c757d' }}">
                                            {{ $task->priority->name }}
                                        </span>
                                    </td>
                                    <td>{{ $task->assignee->name ?? 'Unassigned' }}</td>
                                    <td>{{ $task->updated_at->format('d.m.Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-4">
                        <p>No tasks found with this label.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection