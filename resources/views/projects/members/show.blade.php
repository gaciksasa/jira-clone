@extends('layouts.app')

@section('title', $member->name . ' - ' . $project->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $member->name }}</h1>
            <p class="text-muted mb-0">{{ $project->name }} | {{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.members.index', $project) }}" class="btn btn-outline-primary">Back to Members</a>
            
            @if($member->id != $project->lead_id && $member->id != Auth::id())
                <form method="POST" action="{{ route('projects.members.remove', [$project, $member]) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this member from the project?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Remove from Project</button>
                </form>
            @endif
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Member Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <div class="avatar-circle mb-3">
                            <span class="avatar-initials">{{ substr($member->name, 0, 1) }}</span>
                        </div>
                    </div>
                    
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8">{{ $member->name }}</dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $member->email }}</dd>
                        
                        <dt class="col-sm-4">Role:</dt>
                        <dd class="col-sm-8">
                            @if($member->id == $project->lead_id)
                                <span class="badge bg-primary">Project Lead</span>
                            @else
                                <span class="badge bg-secondary">Member</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Joined:</dt>
                        <dd class="col-sm-8">
                            {{ $member->created_at->format('d.m.Y') }}
                        </dd>
                    </dl>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Task Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <h3>{{ $assignedTasks->count() }}</h3>
                            <p class="text-muted">Assigned Tasks</p>
                        </div>
                        <div class="col-6">
                            <h3>{{ $reportedTasks->count() }}</h3>
                            <p class="text-muted">Reported Tasks</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <ul class="nav nav-tabs mb-4" id="memberTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="assigned-tab" data-bs-toggle="tab" data-bs-target="#assigned" type="button" role="tab" aria-controls="assigned" aria-selected="true">
                        Assigned Tasks
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reported-tab" data-bs-toggle="tab" data-bs-target="#reported" type="button" role="tab" aria-controls="reported" aria-selected="false">
                        Reported Tasks
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="memberTabsContent">
                <div class="tab-pane fade show active" id="assigned" role="tabpanel" aria-labelledby="assigned-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Assigned Tasks</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Key</th>
                                            <th>Summary</th>
                                            <th>Status</th>
                                            <th>Type</th>
                                            <th>Priority</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($assignedTasks as $task)
                                            <tr>
                                                <td>{{ $task->task_number }}</td>
                                                <td>
                                                    <a href="{{ route('projects.tasks.show', [$project, $task]) }}">
                                                        {{ $task->title }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background-color: {{ $task->status->color ?? '#6c757d' }}">
                                                        {{ $task->status->name }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background-color: {{ $task->type->color ?? '#6c757d' }}">
                                                        {{ $task->type->name }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background-color: {{ $task->priority->color ?? '#6c757d' }}">
                                                        {{ $task->priority->name }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4">No assigned tasks found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="reported" role="tabpanel" aria-labelledby="reported-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Reported Tasks</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Key</th>
                                            <th>Summary</th>
                                            <th>Status</th>
                                            <th>Type</th>
                                            <th>Assignee</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reportedTasks as $task)
                                            <tr>
                                                <td>{{ $task->task_number }}</td>
                                                <td>
                                                    <a href="{{ route('projects.tasks.show', [$project, $task]) }}">
                                                        {{ $task->title }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background-color: {{ $task->status->color ?? '#6c757d' }}">
                                                        {{ $task->status->name }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background-color: {{ $task->type->color ?? '#6c757d' }}">
                                                        {{ $task->type->name }}
                                                    </span>
                                                </td>
                                                <td>{{ $task->assignee->name ?? 'Unassigned' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4">No reported tasks found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 100px;
        height: 100px;
        background-color: #6c757d;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    
    .avatar-initials {
        color: white;
        font-size: 40px;
        line-height: 1;
        font-weight: bold;
    }
</style>
@endsection