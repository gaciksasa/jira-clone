@extends('layouts.app')

@section('title', 'My Subtasks')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Subtasks</h2>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#incomplete" data-bs-toggle="tab">Incomplete ({{ isset($subtasks[0]) ? $subtasks[0]->count() : 0 }})</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#completed" data-bs-toggle="tab">Completed ({{ isset($subtasks[1]) ? $subtasks[1]->count() : 0 }})</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="incomplete">
                            @if(isset($subtasks[0]) && $subtasks[0]->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%"></th>
                                                <th style="width: 30%">Subtask</th>
                                                <th style="width: 30%">Parent Task</th>
                                                <th style="width: 15%">Project</th>
                                                <th style="width: 15%">Status</th>
                                                <th style="width: 5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subtasks[0] as $subtask)
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input subtask-checkbox" type="checkbox" 
                                                                   data-subtask-id="{{ $subtask->id }}"
                                                                   data-project-id="{{ $subtask->task->project_id }}"
                                                                   data-task-id="{{ $subtask->task_id }}">
                                                        </div>
                                                    </td>
                                                    <td>{{ $subtask->title }}</td>
                                                    <td>
                                                        <a href="{{ route('projects.tasks.show', [$subtask->task->project, $subtask->task]) }}">
                                                            {{ $subtask->task->task_number }}: {{ $subtask->task->title }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('projects.show', $subtask->task->project) }}">
                                                            {{ $subtask->task->project->name }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge" style="background-color: {{ $subtask->task->status->color ?? '#6c757d' }}">
                                                            {{ $subtask->task->status->name }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('projects.tasks.show', [$subtask->task->project, $subtask->task]) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-arrow-right"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    You have no incomplete subtasks assigned to you.
                                </div>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="completed">
                            @if(isset($subtasks[1]) && $subtasks[1]->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%"></th>
                                                <th style="width: 30%">Subtask</th>
                                                <th style="width: 30%">Parent Task</th>
                                                <th style="width: 15%">Project</th>
                                                <th style="width: 15%">Completed</th>
                                                <th style="width: 5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subtasks[1] as $subtask)
                                                <tr class="table-light">
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input subtask-checkbox" type="checkbox" 
                                                                   checked
                                                                   data-subtask-id="{{ $subtask->id }}"
                                                                   data-project-id="{{ $subtask->task->project_id }}"
                                                                   data-task-id="{{ $subtask->task_id }}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-decoration-line-through">{{ $subtask->title }}</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('projects.tasks.show', [$subtask->task->project, $subtask->task]) }}">
                                                            {{ $subtask->task->task_number }}: {{ $subtask->task->title }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('projects.show', $subtask->task->project) }}">
                                                            {{ $subtask->task->project->name }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $subtask->completed_at->format('d.m.Y H:i') }}</td>
                                                    <td>
                                                        <a href="{{ route('projects.tasks.show', [$subtask->task->project, $subtask->task]) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-arrow-right"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    You have no completed subtasks.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        // Toggle subtask completion
        document.querySelectorAll('.subtask-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const subtaskId = this.dataset.subtaskId;
                const projectId = this.dataset.projectId;
                const taskId = this.dataset.taskId;
                
                fetch(`/projects/${projectId}/tasks/${taskId}/subtasks/${subtaskId}/toggle-complete`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh the page to show the updated lists
                        window.location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    });
</script>
@endpush
@endsection