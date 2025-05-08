@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $user->name }}</h1>
        <div class="btn-group bg-light">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">Users</a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary">Edit</a>
        </div>
    </div>
    <div class="row mb-4">
        <!-- reports section -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Projects</h5>
                    <h2>{{ $projectsCount }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Tasks</h5>
                    <h2>{{ $assignedTasks->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5>This Week</h5>
                    <h2>{{ \App\Http\Controllers\TimesheetController::formatMinutes($thisWeekTotal) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5>This Month</h5>
                    <h2>{{ \App\Http\Controllers\TimesheetController::formatMinutes($thisMonthTotal) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Time Report Filter Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <form method="GET" action="{{ route('admin.users.show', $user) }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Report Results -->
            <div class="alert alert-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Total Hours:</strong> {{ $formattedUserTotal }}
                    </div>
                    <div>
                        <strong>Period:</strong> {{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}
                    </div>
                </div>
            </div>
            
            <!-- Time Breakdown -->
            <div class="card mb-4">
                <div class="card-header h5">Time Breakdown</div>
                <div class="card-body">
                    <div class="accordion" id="projectBreakdown">
                        @foreach($projectTotals as $projectId => $projectTotal)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $projectId }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $projectId }}" aria-expanded="false" aria-controls="collapse{{ $projectId }}">
                                        {{ $projectTotal['project']->name }} <span class="badge bg-primary mx-2"> {{ count($projectTotal['tasks']) }} tasks </span> - {{ $projectTotal['formatted_total'] }}
                                    </button>
                                </h2>
                                <div id="collapse{{ $projectId }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $projectId }}" data-bs-parent="#projectBreakdown">
                                    <div class="accordion-body">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Task</th>
                                                    <th>Total Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($projectTotal['tasks'] as $taskId => $taskData)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('projects.tasks.show', [$projectTotal['project'], $taskData['task']]) }}">
                                                                {{ $taskData['task']->task_number }} - {{ $taskData['task']->title }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $taskData['formatted_total'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Assigned Tasks Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tasks Assigned</h5>
                    <span class="badge bg-primary">{{ $assignedTasks->count() }}</span>
                </div>
                <div class="card-body">
                    @if($assignedTasks->count() > 0)
                        <div class="list-group">
                            @foreach($assignedTasks as $task)
                            <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="list-group-item list-group-item-action">
                                <h5 class="mb-1">
                                    {{ $task->task_number }}: {{ $task->title }}
                                </h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge" style="background-color: {{ $task->status->color ?? '#6c757d' }}">
                                            {{ $task->status->name }}
                                        </span>
                                        <span class="badge" style="background-color: {{ $task->type->color ?? '#6c757d' }}">
                                            {{ $task->type->name }}
                                        </span>
                                        <span class="badge" style="background-color: {{ $task->priority->color ?? '#6c757d' }}">
                                            {{ $task->priority->name }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <p>This user has no assigned tasks.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header h5">User Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">{{ $user->id }}</dd>
                        
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8">{{ $user->name }}</dd>

                        <dt class="col-sm-4">Department:</dt>
                        <dd class="col-sm-8">
                            @if($user->department)
                                <a href="{{ route('admin.departments.show', $user->department) }}">
                                    {{ $user->department->name }} ({{ $user->department->code }})
                                </a>
                            @else
                                <em>Not assigned to any department</em>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>
                        
                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Role:</dt>
                        <dd class="col-sm-8">
                            @if($user->roles->count() > 0)
                            <div>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                @endforeach
                            </div>
                            @else
                                <p>-</p>
                            @endif
                        </dd>
                        <dt class="col-sm-4">Updated:</dt>
                        <dd class="col-sm-8">{{ $user->updated_at->format('d.m.Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header h5">Projects Led</div>
                <div class="card-body">
                    @if($user->leadProjects->count() > 0)
                        <ul class="list-group">
                            @foreach($user->leadProjects as $project)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a>
                                    <span class="badge bg-primary rounded-pill">{{ $project->key }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>This user is not leading any projects.</p>
                    @endif
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header h5">Projects Assigned</div>
                <div class="card-body">
                    @if($userProjects->count() > 0)
                        <ul class="list-group">
                            @foreach($userProjects as $project)
                                @if(!$user->leadProjects->contains($project->id))
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a>
                                        <span class="badge bg-primary rounded-pill">{{ $project->key }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                        
                        @if($userProjects->count() == $user->leadProjects->count())
                            <p class="mt-3 text-muted">This user is only assigned to projects they lead.</p>
                        @endif
                    @else
                        <p>This user is not assigned to any projects.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn-group, form, .navbar, footer {
            display: none !important;
        }
        
        .card {
            border: none !important;
        }
        
        .card-header {
            background-color: #f8f9fa !important;
            color: #000 !important;
        }
        
        .accordion-button::after {
            display: none !important;
        }
        
        .accordion-collapse {
            display: block !important;
        }
        
        .accordion-button {
            padding: 10px !important;
            background-color: #f8f9fa !important;
        }
    }
</style>
@endsection