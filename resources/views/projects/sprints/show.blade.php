@extends('layouts.app')

@section('title', $sprint->name . ' - ' . $project->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $sprint->name }}</h1>
            <p class="text-muted mb-0">{{ $project->name }} | {{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.sprints.index', $project) }}" class="btn btn-outline-primary">All Sprints</a>
            <a href="{{ route('projects.sprints.backlog', [$project, $sprint]) }}" class="btn btn-outline-primary">Manage Tasks</a>
            <a href="{{ route('projects.sprints.edit', [$project, $sprint]) }}" class="btn btn-outline-primary">Edit</a>
            
            @if($sprint->status == 'planning')
                <form method="POST" action="{{ route('projects.sprints.start', [$project, $sprint]) }}">
                    @csrf
                    <button type="submit" class="btn btn-success">Start Sprint</button>
                </form>
            @elseif($sprint->status == 'active')
                <form method="POST" action="{{ route('projects.sprints.complete', [$project, $sprint]) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Complete Sprint</button>
                </form>
            @endif
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Sprint Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($sprint->status == 'planning')
                                <span class="badge bg-secondary">Planning</span>
                            @elseif($sprint->status == 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-primary">Completed</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Start Date</dt>
                        <dd class="col-sm-8">{{ $sprint->start_date ? $sprint->start_date->format('M d, Y') : 'Not started' }}</dd>
                        
                        <dt class="col-sm-4">End Date</dt>
                        <dd class="col-sm-8">{{ $sprint->end_date ? $sprint->end_date->format('M d, Y') : 'Not set' }}</dd>
                        
                        <dt class="col-sm-4">Tasks</dt>
                        <dd class="col-sm-8">{{ $sprint->tasks()->count() }}</dd>
                        
                        <dt class="col-sm-4">Created</dt>
                        <dd class="col-sm-8">{{ $sprint->created_at->format('M d, Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Sprint Progress</div>
                <div class="card-body">
                    @php
                        $totalTasks = $sprint->tasks()->count();
                        $completedTasks = $sprint->tasks()->whereHas('status', function($query) {
                            $query->where('slug', 'done');
                        })->count();
                        $progressPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                    @endphp
                    
                    <h6>{{ $completedTasks }} of {{ $totalTasks }} tasks completed ({{ $progressPercentage }}%)</h6>
                    <div class="progress mb-4">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progressPercentage }}%;" aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100">{{ $progressPercentage }}%</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h5>{{ $sprint->tasks()->whereHas('status', function($query) {
                                $query->where('slug', 'to-do');
                            })->count() }}</h5>
                            <p class="text-muted">To Do</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h5>{{ $sprint->tasks()->whereHas('status', function($query) {
                                $query->whereIn('slug', ['in-progress', 'in-review']);
                            })->count() }}</h5>
                            <p class="text-muted">In Progress</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h5>{{ $completedTasks }}</h5>
                            <p class="text-muted">Done</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">Sprint Board</div>
        <div class="card-body p-2">
            <div class="row">
                @foreach($statuses as $status)
                    <div class="col">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">{{ $status->name }}</h5>
                            </div>
                            <div class="card-body" data-status-id="{{ $status->id }}">
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
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kanbanColumns = document.querySelectorAll('.kanban-column');
        const currentUserId = parseInt('{{ auth()->id() }}');
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
                    const assigneeId = parseInt(taskCard.dataset.assigneeId || '0');
                    
                    // Only allow dragging if user is admin, project manager, or the task assignee
                    if (isAdmin || isProjectManager || assigneeId === currentUserId) {
                        return true;
                    } else {
                        // Show notification instead of alert for better UX
                        if (!taskCard.classList.contains('non-draggable')) {
                            showNotification('You can only move tasks assigned to you', 'warning');
                        }
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
        
        const projectId = {{ $project->id }};
        
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