@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $project->name }}</h2>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.board', $project) }}" class="btn btn-outline-primary">Board</a>
            <!--<a href="{{ route('projects.tasks.index', $project) }}" class="btn btn-outline-primary">Tasks</a>
            <a href="{{ route('projects.sprints.index', $project) }}" class="btn btn-outline-primary">Sprints</a>
            <a href="{{ route('projects.members.index', $project) }}" class="btn btn-outline-primary">Members</a>-->
            @can('manage projects')
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-primary">Edit</a>
            @endcan
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header h5">Project Details</div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Project Key:</dt>
                        <dd class="col-sm-9">{{ $project->key }}</dd>
                        
                        <dt class="col-sm-3">Description:</dt>
                        <dd class="col-sm-9">{!! $project->description ?: '<em>No description provided</em>' !!}</dd>
                        
                        <dt class="col-sm-3">Project Lead:</dt>
                        <dd class="col-sm-9">{{ $project->lead->name }} ({{ $project->lead->email }})</dd>
                        
                        <dt class="col-sm-3">Department:</dt>
                        <dd class="col-sm-9">
                            @if($project->department)
                                {{ $project->department->name }} ({{ $project->department->code }})
                            @else
                                <span>Not assigned to any department</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-3">Created:</dt>
                        <dd class="col-sm-9">{{ $project->created_at->format('d.m.Y H:i:s') }}</dd>
                        
                        <dt class="col-sm-3">Updated:</dt>
                        <dd class="col-sm-9">{{ $project->updated_at->format('d.m.Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Project Tasks</h5>
                    <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-primary">Create Task</a>
                </div>
                <div class="card-body">
                    @if($tasks->count() > 0)
                        <div class="list-group">
                            @foreach($tasks as $task)
                                <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $task->task_number }}: {{ $task->title }}</h6>
                                        <small>{{ $task->updated_at->diffForHumans() }}</small>
                                    </div>
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
                                        <small>{{ $task->assignee->name ?? 'Unassigned' }}</small>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center">No tasks in this project yet.</p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-primary">Create First Task</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header h5">Project Stats</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center border-end">
                            <h3>{{ $tasks->count() }}</h3>
                            <p class="text-muted">Total Tasks</p>
                        </div>
                        <div class="col-6 text-center">
                            <h3>{{ $tasks->where('task_status_id', $statuses->where('slug', 'done')->first()->id ?? 0)->count() }}</h3>
                            <p class="text-muted">Completed</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Project Members Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Project Members</h5>
                    @can('update', $project)
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                            Add Member
                        </button>
                    @endcan
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($project->members as $member)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $member->name }}</span>
                                <div>
                                    @if($member->id == $project->lead_id)
                                        <span class="badge bg-primary">Project Lead</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            @if(auth()->user()->id === $project->lead_id || auth()->user()->can('manage users'))
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Upcoming Team Time Off</h5>
                        <a href="{{ route('vacation.index') }}?team={{ $project->id }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        @php
                            $memberIds = $project->members->pluck('id')->toArray();
                            $upcomingVacations = App\Models\VacationRequest::whereIn('user_id', $memberIds)
                                ->where('status', 'approved')
                                ->where('start_date', '>=', now())
                                ->orderBy('start_date')
                                ->with('user')
                                ->take(3)
                                ->get();
                        @endphp
                        
                        @if($upcomingVacations->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($upcomingVacations as $vacation)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $vacation->user->name }}</strong>
                                            <span class="badge {{ $vacation->type == 'vacation' ? 'bg-primary' : ($vacation->type == 'sick_leave' ? 'bg-danger' : 'bg-warning') }} ms-2">
                                                {{ ucfirst(str_replace('_', ' ', $vacation->type)) }}
                                            </span>
                                        </div>
                                        <span>{{ $vacation->start_date->format('M d') }} - {{ $vacation->end_date->format('M d') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="p-3 text-center text-muted">
                                No upcoming time off scheduled
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Labels</h5>
                    <a href="{{ route('projects.labels.create', $project) }}" class="btn btn-sm btn-primary">Add Label</a>
                </div>
                <div class="card-body">
                    @if($project->labels->count() > 0)
                        @foreach($project->labels as $label)
                            <a href="{{ route('projects.tasks.by-label', [$project, $label]) }}" class="text-decoration-none">
                                <span class="badge mb-2 me-2" style="background-color: {{ $label->color }}">{{ $label->name }}</span>
                            </a>
                        @endforeach
                    @else
                        <p class="text-center text-muted mb-0">No labels defined yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@can('update', $project)
<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMemberModalLabel">Add Member to Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('projects.members.add', $project) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Select User</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Select a user</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Member</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

