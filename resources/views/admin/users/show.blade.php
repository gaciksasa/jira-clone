@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $user->name }}</h1>
        <div class="btn-group">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">Users</a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary">Edit</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Assigned Tasks </h5>
                        <span class="badge bg-primary">{{ $user->assignedTasks->count() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($user->assignedTasks->count() > 0)
                        <div class="list-group">
                            @foreach($user->assignedTasks as $task)
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
                <div class="card-header h5">Basic Information</div>
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
        </div>
    </div>
</div>
@endsection