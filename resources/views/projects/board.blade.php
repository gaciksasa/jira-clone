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
            <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-outline-primary">Create Task</a>
            <a href="{{ route('projects.statuses.index', $project) }}" class="btn btn-primary">Manage Board</a>
        </div>
    </div>
    
    <!-- Active board columns -->
    <div class="row board-container">
        @foreach($statuses as $status)
            <div class="col">
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $status->name }}</h5>
                            <span class="badge bg-primary">{{ isset($tasks[$status->id]) ? count($tasks[$status->id]) : 0 }}</span>
                        </div>
                    </div>
                    <div class="card-body" data-status-id="{{ $status->id }}">
                        @if(isset($tasks[$status->id]) && count($tasks[$status->id]) > 0)
                            @foreach($tasks[$status->id] as $task)
                                <div class="card task-card {{ (!auth()->user()->canMoveTask($task)) ? 'non-draggable' : '' }}" 
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
    
    <!-- Closed tasks in a separate row -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Closed tasks</h5>
                        <span class="badge bg-primary">{{ $closedTasks->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-2">
                    @if($closedTasks->count() > 0)
                        <div class="row">
                            @foreach($closedTasks->take(10) as $task)
                                <div class="col-md-3 mb-3">
                                    <div class="card task-card non-draggable bg-light" data-task-id="{{ $task->id }}">
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
                                </div>
                            @endforeach
                        </div>
                        
                        @if($closedTasks->count() > 10)
                            <div class="text-center mt-3">
                                <a href="{{ route('projects.tasks.index', ['project' => $project, 'filter' => 'closed']) }}" class="btn btn-sm btn-outline-secondary">
                                    View all {{ $closedTasks->count() }} closed tasks
                                </a>
                            </div>
                        @endif
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

@php
// Helper function to determine if a color is light or dark
function isLightColor($hexColor) {
    // Convert hex to RGB
    $hexColor = ltrim($hexColor, '#');
    $r = hexdec(substr($hexColor, 0, 2));
    $g = hexdec(substr($hexColor, 2, 2));
    $b = hexdec(substr($hexColor, 4, 2));
    
    // Calculate luminance
    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    
    return $luminance > 0.5;
}
@endphp

@push('styles')
<style>
    .board-container {
        overflow-x: auto;
        padding-bottom: 15px;
    }

    .col.px-1 {
        padding-left: 4px !important; /* Reduced horizontal margin */
        padding-right: 4px !important;
    }
    
    .board-container > .col:first-child {
        padding-left: 12px !important;
    }
    
    .board-container > .col:last-child {
        padding-right: 12px !important;
    }
    
    .col {
        float: none;
        display: inline-block;
        vertical-align: top;
    }
    
    .task-card {
        cursor: grab;
        margin-bottom: 10px;
        transition: all 0.2s ease;
    }
    
    .task-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .task-card.non-draggable {
        cursor: not-allowed !important;
        opacity: 0.75;
    }
    
    .task-type-icon {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    
    .priority-label {
        font-size: 12px;
        padding: 2px 6px;
        border-radius: 3px;
        color: white;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kanbanColumns = document.querySelectorAll('.kanban-column');
        const currentUserId = {{ auth()->id() }};
        const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};
        const isProjectManager = {{ auth()->user()->hasRole('project_manager') ? 'true' : 'false' }};
        const projectId = {{ $project->id }};
        
        kanbanColumns.forEach(column => {
            new Sortable(column, {
                group: 'tasks',
                animation: 150,
                ghostClass: 'bg-light',
                filter: '.non-draggable', // Prevent dragging elements with non-draggable class
                onMove: function(evt) {
                    const taskCard = evt.dragged;
                    const assigneeId = parseInt(taskCard.dataset.assigneeId) || 0;
                    
                    // Only allow dragging if user is admin, project manager, or the task assignee
                    if (isAdmin || isProjectManager || assigneeId === currentUserId) {
                        return true;
                    } else {
                        // Notify user why they can't move this task
                        showNotification('You can only move tasks assigned to you', 'warning');
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
                    fetch(`/projects/${projectId}/tasks/${taskId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
                            showNotification('Task status updated successfully', 'success');
                        } else if (data.error) {
                            showNotification(data.error, 'error');
                            // Revert the drag by refreshing the page
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred while updating the task', 'error');
                        // Revert the drag by refreshing the page
                        window.location.reload();
                    });
                }
            });
        });

        // Simple notification function
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Add to body
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }
    });
</script>
@endpush