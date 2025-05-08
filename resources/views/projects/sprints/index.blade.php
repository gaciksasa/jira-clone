@extends('layouts.app')

@section('title', $project->name . ' - Sprints')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $project->name }} Sprints</h2>
            <p class="text-muted mb-0">{{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Overview</a>
            <a href="{{ route('projects.board', $project) }}" class="btn btn-outline-primary">Board</a>
            <a href="{{ route('projects.sprints.create', $project) }}" class="btn btn-primary">Create Sprint</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('projects.sprints.index', $project) }}">All Sprints</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('status') == 'active' ? 'active' : '' }}" href="{{ route('projects.sprints.index', ['project' => $project, 'status' => 'active']) }}">Active</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('status') == 'completed' ? 'active' : '' }}" href="{{ route('projects.sprints.index', ['project' => $project, 'status' => 'completed']) }}">Completed</a>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Tasks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sprints as $sprint)
                            <tr>
                                <td>
                                    <a href="{{ route('projects.sprints.show', [$project, $sprint]) }}">
                                        {{ $sprint->name }}
                                    </a>
                                </td>
                                <td>
                                    @if($sprint->status == 'planning')
                                        <span class="badge bg-secondary">Planning</span>
                                    @elseif($sprint->status == 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-primary">Completed</span>
                                    @endif
                                </td>
                                <td>{{ $sprint->start_date ? $sprint->start_date->format('d.m.Y') : 'Not started' }}</td>
                                <td>{{ $sprint->end_date ? $sprint->end_date->format('d.m.Y') : 'Not set' }}</td>
                                <td>{{ $sprint->tasks_count }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('projects.sprints.show', [$project, $sprint]) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="{{ route('projects.sprints.backlog', [$project, $sprint]) }}" class="btn btn-sm btn-outline-primary">Manage Tasks</a>
                                        <a href="{{ route('projects.sprints.edit', [$project, $sprint]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        
                                        @if($sprint->status == 'planning')
                                            <form method="POST" action="{{ route('projects.sprints.start', [$project, $sprint]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Start Sprint</button>
                                            </form>
                                        @elseif($sprint->status == 'active')
                                            <form method="POST" action="{{ route('projects.sprints.complete', [$project, $sprint]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">Complete Sprint</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No sprints found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection