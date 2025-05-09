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
                            <a class="nav-link active" href="#incomplete" data-bs-toggle="tab">Incomplete ({{ isset($subtasks['incomplete']) ? $subtasks['incomplete']->count() : 0 }})</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#completed" data-bs-toggle="tab">Completed ({{ isset($subtasks['completed']) ? $subtasks['completed']->count() : 0 }})</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="incomplete">
                            @if(isset($subtasks['incomplete']) && $subtasks['incomplete']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%"></th>
                                                <th style="width: 15%">Key</th>
                                                <th style="width: 25%">Subtask</th>
                                                <th style="width: 25%">Parent Task</th>
                                                <th style="width: 15%">Project</th>
                                                <th style="width: 10%">Status</th>
                                                <th style="width: 5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subtasks['incomplete'] as $subtask)
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input subtask-checkbox" type="checkbox" 
                                                                   data-subtask-id="{{ $subtask->id }}"
                                                                   data-project-id="{{ $subtask->project_id }}"
                                                                   data-task-id="{{ $subtask->id }}">
                                                        </div>
                                                    </td>
                                                    <td>{{ $subtask->task_number }}</td>
                                                    <td>
                                                        <a href="{{ route('projects.tasks.show', [$subtask->project, $subtask]) }}">
                                                            {{ $subtask->title }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('projects.tasks.show', [$subtask->project, $subtask->parent]) }}">
                                                            {{ $subtask->parent->task_number }}: {{ $subtask->parent->title }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('projects.show', $subtask->project) }}">
                                                            {{ $subtask->project->name }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge" style="background-color: {{ $subtask->status->color ?? '#6c757d' }}">
                                                            {{ $subtask->status->name }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('projects.tasks.show', [$subtask->project, $subtask]) }}" class="btn btn-sm btn-outline-primary">
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
                            @if(isset($subtasks['completed']) && $subtasks['completed']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%"></th>
                                                <th style="width: 15%">Key</th>
                                                <th style="width: 25%">Subtask</th>
                                                <th style="width: 25%">Parent Task</th>
                                                <th style="width: 15%">Project</th>
                                                <th style="width: 10%">Completed</th>
                                                <th style="width: 5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subtasks['completed'] as $subtask)
                                                <tr class="table-light">
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input subtask-checkbox" type="checkbox" 
                                                                   checked
                                                                   data-subtask-id="{{ $subtask->id }}"
                                                                   data-project-id="{{ $subtask->project_id }}"
                                                                   data-task-id="{{ $subtask->id }}">
                                                        </div>
                                                    </td>
                                                    <td>{{ $subtask->task_number }}</td>
                                                    <td>
                                                        <span class="text-decoration-line-through">{{ $subtask->title }}</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('projects.tasks.show', [$subtask->project, $subtask->parent]) }}">
                                                            {{ $subtask->parent->task_number }}: {{ $subtask->parent->title }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('projects.show', $subtask->project) }}">
                                                            {{ $subtask->project->name }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $subtask->closed_at->format('d.m.Y H:i') }}</td>
                                                    <td>
                                                        <a href="{{ route('projects.tasks.show', [$subtask->project, $subtask]) }}" class="btn btn-sm btn-outline-primary">
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
                
                // Determine which action to take based on the checkbox state
                const action = this.checked ? 'close' : 'reopen';
                
                fetch(`/projects/${projectId}/tasks/${subtaskId}/${action}`, {
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