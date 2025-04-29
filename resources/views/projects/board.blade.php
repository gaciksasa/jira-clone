@extends('layouts.app')

@section('title', $project->name . ' Board')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $project->name }} Board</h1>
            <p class="text-muted mb-0">{{ $project->key }} | Project Lead: {{ $project->lead->name }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Overview</a>
            <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-primary">Create Task</a>
        </div>
    </div>
    
    <div class="row">
        @foreach($statuses as $status)
            <div class="col">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ $status->name }}</h5>
                    </div>
                    <div class="card-body p-2 kanban-column" data-status-id="{{ $status->id }}">
                        @if(isset($tasks[$status->id]) && count($tasks[$status->id]) > 0)
                            @foreach($tasks[$status->id] as $task)
                                <div class="card task-card" data-task-id="{{ $task->id }}">
                                    <div class="card-body p-2">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="task-type-icon" style="background-color: {{ $task->type->color }};" title="{{ $task->type->name }}"></span>
                                            <small class="text-muted">{{ $task->task_number }}</small>
                                        </div>
                                        <h6 class="card-title mb-2">{{ $task->title }}</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="priority-label" style="background-color: {{ $task->priority->color }};">
                                                {{ $task->priority->name }}
                                            </span>
                                            <div>
                                                @if($task->assignee)
                                                    <small class="text-muted">{{ $task->assignee->name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="stretched-link"></a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-5">
                                <p>No tasks</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kanbanColumns = document.querySelectorAll('.kanban-column');
        
        kanbanColumns.forEach(column => {
            new Sortable(column, {
                group: 'tasks',
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: function(evt) {
                    const taskId = evt.item.dataset.taskId;
                    const newStatusId = evt.to.dataset.statusId;
                    
                    // Update the task status via AJAX
                    const url = `/projects/${projectId}/tasks/${taskId}/status`;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    
                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            task_status_id: newStatusId
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to update task status');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Task status updated successfully');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Revert the drag by refreshing the page
                        window.location.reload();
                    });
                }
            });
        });
        
        const projectId = {{ $project->id }};
    });
</script>
@endpush