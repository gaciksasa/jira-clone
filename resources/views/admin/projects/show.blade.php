@extends('layouts.app')

@section('title', 'Project Details - ' . $project->name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-3">
            @include('layouts.admin-nav', ['project' => $project])
        </div>
        
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>{{ $project->name }}</h1>
                    <p class="text-muted mb-0">{{ $project->key }}</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-primary">Back to Projects</a>
                    <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-outline-secondary">Edit</a>
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-info">View Frontend</a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">Project Details</div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-3">Project Key:</dt>
                                <dd class="col-sm-9">{{ $project->key }}</dd>
                                
                                <dt class="col-sm-3">Description:</dt>
                                <dd class="col-sm-9">{!! nl2br(e($project->description)) ?: '<em>No description provided</em>' !!}</dd>
                                
                                <dt class="col-sm-3">Project Lead:</dt>
                                <dd class="col-sm-9">{{ $project->lead->name }} ({{ $project->lead->email }})</dd>
                                
                                <dt class="col-sm-3">Department:</dt>
                                <dd class="col-sm-9">
                                    @if($project->department)
                                        {{ $project->department->name }} ({{ $project->department->code }})
                                    @else
                                        <em>Not assigned to any department</em>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-3">Created:</dt>
                                <dd class="col-sm-9">{{ $project->created_at->format('M d, Y H:i:s') }}</dd>
                                
                                <dt class="col-sm-3">Last Updated:</dt>
                                <dd class="col-sm-9">{{ $project->updated_at->format('M d, Y H:i:s') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Project Members ({{ $project->members->count() }})</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($project->members as $member)
                                            <tr>
                                                <td>{{ $member->name }}</td>
                                                <td>{{ $member->email }}</td>
                                                <td>
                                                    @if($member->id == $project->lead_id)
                                                        <span class="badge bg-primary">Project Lead</span>
                                                    @else
                                                        <span class="badge bg-secondary">Member</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">No members found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">Project Statistics</div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4 border-end">
                                    <h3>{{ $project->tasks->count() }}</h3>
                                    <p>Tasks</p>
                                </div>
                                <div class="col-4 border-end">
                                    <h3>{{ $project->tasks->where('closed_at', null)->count() }}</h3>
                                    <p>Open Tasks</p>
                                </div>
                                <div class="col-4 border-end">
                                    <h3>{{ $project->tasks->whereNotNull('closed_at')->count() }}</h3>
                                    <p>Closed Tasks</p>
                                </div>
                                <div class="col-4">
                                    <h3>{{ $project->formattedTotalTime() }}</h3>
                                    <p>Total Time</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">Board Columns</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Order</th>
                                            <th>Tasks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($project->taskStatuses as $status)
                                            <tr>
                                                <td>
                                                    <span class="badge" style="background-color: {{ $status->color ?? '#6c757d' }}">
                                                        {{ $status->name }}
                                                    </span>
                                                </td>
                                                <td>{{ $status->order }}</td>
                                                <td>{{ $project->tasks->where('task_status_id', $status->id)->count() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">No board columns found</td>
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
@endsection