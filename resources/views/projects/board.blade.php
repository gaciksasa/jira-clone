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
                                <div class="card task-card {{ (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('project_manager') && $task->assignee_id !== auth()->id()) ? 'non-draggable' : '' }}" 
                                     data-task-id="{{ $task->id }}" 
                                     data-assignee-id="{{ $task->assignee_id }}">
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
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="stretched-link"></a>
                                            <form method="POST" action="{{ route('projects.tasks.close', [$project, $task]) }}" class="position-relative" style="z-index: 10;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">Close</button>
                                            </form>
                                        </div>
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
        
        <!-- Closed Tasks Column -->
        <div class="col">
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Closed</h5>
                </div>
                <div class="card-body p-2">
                    @if($closedTasks->count() > 0)
                        @foreach($closedTasks as $task)
                            <div class="card task-card non-draggable bg-light" data-task-id="{{ $task->id }}">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="task-type-icon" style="background-color: {{ $task->type->color }};" title="{{ $task->type->name }}"></span>
                                        <small class="text-muted">{{ $task->task_number }}</small>
                                    </div>
                                    <h6 class="card-title mb-2 text-decoration-line-through">{{ $task->title }}</h6>
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
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small class="text-muted">Closed: {{ $task->closed_at->format('M d, Y') }}</small>
                                        <form method="POST" action="{{ route('projects.tasks.reopen', [$project, $task]) }}" class="position-relative" style="z-index: 10;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success">Reopen</button>
                                        </form>
                                    </div>
                                    <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="stretched-link"></a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-5">
                            <p>No closed tasks</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .non-draggable {
        cursor: not-allowed !important;
        opacity: 0.75;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kanbanColumns = document.querySelectorAll('.kanban-column');
        const currentUserId = {{ auth()->id() }};
        const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};
        const isProjectManager = {{ auth()->user()->hasRole('project_manager') ? 'true' : 'false' }};
        
        kanbanColumns.forEach(column => {
            new Sortable(column, {
                group: 'tasks',
                animation: 150,
                ghostClass: 'bg-light',
                filter: '.non-draggable', // Prevent dragging elements with non-draggable class
                onMove: function(evt) {
                    const taskCard = evt.dragged;
                    const assigneeId = taskCard.dataset.assigneeId;
                    
                    // Only allow dragging if user is admin, project manager, or the task assignee
                    if (isAdmin || isProjectManager || (assigneeId && parseInt(assigneeId) === currentUserId)) {
                        return true;
                    } else {
                        // Prevent the move and show a tooltip or message
                        alert('You can only move tasks assigned to you.');
                        return false;
                    }
                },
                onEnd: function(evt) {
                    if (evt.from === evt.to && evt.oldIndex === evt.newIndex) {
                        // If task was not actually moved, do nothing
                        return;
                    }
                    
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
                        if (data.success) {
                            console.log('Task status updated successfully');
                        } else if (data.error) {
                            alert(data.error);
                            // Revert the drag by refreshing the page
                            window.location.reload();
                        }
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