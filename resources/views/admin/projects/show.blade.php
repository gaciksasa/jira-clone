@extends('layouts.app')

@section('title', 'Project Details - ' . $project->name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>{{ $project->name }} Time Report</h2>
                <div class="btn-group">
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
                                <dt class="col-sm-4">Name:</dt>
                                <dd class="col-sm-8">{{ $project->name }}</dd>

                                <dt class="col-sm-4">Key:</dt>
                                <dd class="col-sm-8">{{ $project->key }}</dd>
                                
                                <dt class="col-sm-4">Project Lead:</dt>
                                <dd class="col-sm-8">{{ $project->lead->name }}</dd>
                                
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
                                
                                <dt class="col-sm-4">Updated:</dt>
                                <dd class="col-sm-8">{{ $project->updated_at->format('d.m.Y H:i:s') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Project Members</h5>
                            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-outline-primary">Manage Members</a>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <!-- First show the project lead -->
                                @foreach($project->members as $member)
                                    @if($member->id === $project->lead_id)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>{{ $member->name }}</strong>
                                            <span class="badge bg-primary ms-2">Project Lead</span>
                                        </li>
                                    @endif
                                @endforeach
                                
                                <!-- Then show all other members -->
                                @foreach($project->members->sortBy('name') as $member)
                                    @if($member->id !== $project->lead_id)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                {{ $member->name }}
                                            </div>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
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