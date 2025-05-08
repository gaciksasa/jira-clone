@extends('layouts.app')

@section('title', 'Project Details - ' . $project->name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>{{ $project->name }} Time Report</h1>
                    <p class="text-muted mb-0">{{ $project->key }}</p>
                </div>
                <div class="btn-group bg-light">
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-primary">Projects</a>
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Tasks</a>
                    <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-outline-primary">Edit</a>
                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">Print Report</button>

                </div>
            </div>
            
            <!-- Project Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Tasks</h5>
                            <h2>{{ $project->tasks->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Open Tasks</h5>
                            <h2>{{ $project->tasks->where('closed_at', null)->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Members</h5>
                            <h2>{{ $project->members->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Time</h5>
                            <h2>{{ $formattedProjectTotal }}</h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <!-- Time Report Content -->
                    @include('admin.projects._time_report')
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header h5">Project Details</div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Project Key:</dt>
                                <dd class="col-sm-8">{{ $project->key }}</dd>
                                
                                <dt class="col-sm-4">Description:</dt>
                                <dd class="col-sm-8">{!! nl2br(e($project->description)) ?: '<em>No description provided</em>' !!}</dd>
                                
                                <dt class="col-sm-4">Project Lead:</dt>
                                <dd class="col-sm-8">{{ $project->lead->name }} ({{ $project->lead->email }})</dd>
                                
                                <dt class="col-sm-4">Department:</dt>
                                <dd class="col-sm-8">
                                    @if($project->department)
                                        {{ $project->department->name }} ({{ $project->department->code }})
                                    @else
                                        <span>Not assigned to any department</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">Created:</dt>
                                <dd class="col-sm-8">{{ $project->created_at->format('d.m.Y H:i:s') }}</dd>
                                
                                <dt class="col-sm-4">Last Updated:</dt>
                                <dd class="col-sm-8">{{ $project->updated_at->format('d.m.Y H:i:s') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header h5">Project Members</div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach($project->members as $member)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <a href="{{ route('admin.users.show', $member) }}">{{ $member->name }}</a>
                                        </div>
                                        <div>
                                            @if($member->id == $project->lead_id)
                                                <span class="badge bg-primary">Project Lead</span>
                                            @else
                                                <span class="badge bg-secondary">Member</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header h5">Board Status</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Column</th>
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
                                                <td>{{ $project->tasks->where('task_status_id', $status->id)->count() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center">No board columns found</td>
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