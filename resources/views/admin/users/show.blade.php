@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>User Details</h1>
        <div class="btn-group">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">Back to Users</a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-secondary">Edit</a>
            
            @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }}">
                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
                
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user?');" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">ID:</dt>
                        <dd class="col-sm-9">{{ $user->id }}</dd>
                        
                        <dt class="col-sm-3">Name:</dt>
                        <dd class="col-sm-9">{{ $user->name }}</dd>
                        
                        <dt class="col-sm-3">Email:</dt>
                        <dd class="col-sm-9">{{ $user->email }}</dd>
                        
                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-3">Created:</dt>
                        <dd class="col-sm-9">{{ $user->created_at->format('M d, Y H:i:s') }}</dd>
                        
                        <dt class="col-sm-3">Updated:</dt>
                        <dd class="col-sm-9">{{ $user->updated_at->format('M d, Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Roles & Permissions</div>
                <div class="card-body">
                    <h5>Roles:</h5>
                    @if($user->roles->count() > 0)
                        <div class="mb-3">
                            @foreach($user->roles as $role)
                                <span class="badge bg-primary me-1">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <p>This user has no roles assigned.</p>
                    @endif
                    
                    <h5>Permissions:</h5>
                    @if($user->getPermissionsViaRoles()->count() > 0)
                        <div>
                            @foreach($user->getPermissionsViaRoles() as $permission)
                                <span class="badge bg-secondary me-1">{{ $permission->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <p>This user has no permissions via roles.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Projects Led</div>
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
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Assigned Tasks</div>
                <div class="card-body">
                    @if($user->assignedTasks->count() > 0)
                        <ul class="list-group">
                            @foreach($user->assignedTasks->take(5) as $task)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}">{{ $task->task_number }}: {{ $task->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                        
                        @if($user->assignedTasks->count() > 5)
                            <p class="text-center mt-3">
                                <em>Showing 5 of {{ $user->assignedTasks->count() }} tasks</em>
                            </p>
                        @endif
                    @else
                        <p>This user has no assigned tasks.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection