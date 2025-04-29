@extends('layouts.app')

@section('title', $sprint->name . ' - Backlog Management')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $sprint->name }} - Manage Tasks</h1>
            <p class="text-muted mb-0">{{ $project->name }} | {{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.sprints.show', [$project, $sprint]) }}" class="btn btn-outline-primary">Sprint Details</a>
            <a href="{{ route('projects.sprints.index', $project) }}" class="btn btn-outline-primary">All Sprints</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Project Backlog</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('projects.sprints.tasks.add', [$project, $sprint]) }}" id="addTasksForm">
                        @csrf
                        
                        @if($backlogTasks->count() > 0)
                            <div class="mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="selectAllBacklog">
                                    <label class="form-check-label" for="selectAllBacklog">
                                        Select All
                                    </label>
                                </div>
                                
                                <div class="list-group">
                                    @foreach($backlogTasks as $task)
                                        <div class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <div class="form-check">
                                                    <input class="form-check-input backlog-task-checkbox" type="checkbox" name="task_ids[]" value="{{ $task->id }}" id="task-{{ $task->id }}">
                                                    <label class="form-check-label" for="task-{{ $task->id }}">
                                                        <strong>{{ $task->task_number }}</strong>: {{ $task->title }}
                                                    </label>
                                                </div>
                                                <div>
                                                    <span class="badge" style="background-color: {{ $task->type->color ?? '#6c757d' }}">
                                                        {{ $task->type->name }}
                                                    </span>
                                                    <span class="badge" style="background-color: {{ $task->priority->color ?? '#6c757d' }}">
                                                        {{ $task->priority->name }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Add Selected Tasks to Sprint</button>
                            </div>
                        @else
                            <div class="text-center">
                                <p>No tasks in the backlog.</p>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Sprint Tasks</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('projects.sprints.tasks.remove', [$project, $sprint]) }}" id="removeTasksForm">
                        @csrf
                        @method('DELETE')
                        
                        @if($sprintTasks->count() > 0)
                            <div class="mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="selectAllSprint">
                                    <label class="form-check-label" for="selectAllSprint">
                                        Select All
                                    </label>
                                </div>
                                
                                <div class="list-group">
                                    @foreach($sprintTasks as $task)
                                        <div class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <div class="form-check">
                                                    <input class="form-check-input sprint-task-checkbox" type="checkbox" name="task_ids[]" value="{{ $task->id }}" id="sprint-task-{{ $task->id }}">
                                                    <label class="form-check-label" for="sprint-task-{{ $task->id }}">
                                                        <strong>{{ $task->task_number }}</strong>: {{ $task->title }}
                                                    </label>
                                                </div>
                                                <div>
                                                    <span class="badge" style="background-color: {{ $task->status->color ?? '#6c757d' }}">
                                                        {{ $task->status->name }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger">Remove Selected Tasks from Sprint</button>
                            </div>
                        @else
                            <div class="text-center">
                                <p>No tasks in this sprint.</p>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle "Select All" for backlog
        const selectAllBacklog = document.getElementById('selectAllBacklog');
        const backlogCheckboxes = document.querySelectorAll('.backlog-task-checkbox');
        
        if (selectAllBacklog) {
            selectAllBacklog.addEventListener('change', function() {
                const isChecked = this.checked;
                backlogCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });
        }
        
        // Handle "Select All" for sprint tasks
        const selectAllSprint = document.getElementById('selectAllSprint');
        const sprintCheckboxes = document.querySelectorAll('.sprint-task-checkbox');
        
        if (selectAllSprint) {
            selectAllSprint.addEventListener('change', function() {
                const isChecked = this.checked;
                sprintCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });
        }
    });
</script>
@endpush