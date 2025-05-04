@extends('layouts.app')

@section('title', $project->name . ' - Tasks')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $project->name }} Tasks</h1>
            <p class="text-muted mb-0">{{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Overview</a>
            <a href="{{ route('projects.board', $project) }}" class="btn btn-outline-primary">Board</a>
            <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-primary">Create Task</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('projects.tasks.index', $project) }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search tasks..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        @foreach($project->taskStatuses as $status)
                            <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        @foreach(App\Models\TaskType::all() as $type)
                            <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="assignee">
                        <option value="">All Assignees</option>
                        <option value="unassigned" {{ request('assignee') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                        @foreach($project->members as $member)
                            <option value="{{ $member->id }}" {{ request('assignee') == $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('projects.tasks.index', $project) }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                @php
                    // Split tasks into open and closed
                    $openTasks = $tasks->filter(function($task) {
                        return $task->closed_at === null;
                    });
                    
                    $closedTasks = $tasks->filter(function($task) {
                        return $task->closed_at !== null;
                    });
                @endphp
                
                @if($openTasks->count() > 0 || $closedTasks->count() > 0)
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Assignee</th>
                                <th>Time Spent</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Open Tasks -->
                            @foreach($openTasks as $task)
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
                                    <td>{{ $task->formattedTotalTime() }}</td>
                                    <td>{{ $task->created_at->format('M d, Y') }}</td>
                                    <td>{{ $task->updated_at->format('M d, Y') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('projects.tasks.close', [$project, $task]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Close</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            
                            <!-- Closed Tasks (if any) -->
                            @if($closedTasks->count() > 0)
                                <tr class="table-secondary">
                                    <td colspan="9" class="text-center fw-bold">Closed Tasks</td>
                                </tr>
                                @foreach($closedTasks as $task)
                                    <tr class="table-light">
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
                                            <span class="badge bg-secondary">
                                                Closed
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $task->priority->color ?? '#6c757d' }}">
                                                {{ $task->priority->name }}
                                            </span>
                                        </td>
                                        <td>{{ $task->assignee->name ?? 'Unassigned' }}</td>
                                        <td>{{ $task->formattedTotalTime() }}</td>
                                        <td>{{ $task->created_at->format('M d, Y') }}</td>
                                        <td>{{ $task->closed_at->format('M d, Y') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('projects.tasks.reopen', [$project, $task]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success">Reopen</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-4">
                        <p>No tasks found.</p>
                        <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-primary">Create First Task</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection