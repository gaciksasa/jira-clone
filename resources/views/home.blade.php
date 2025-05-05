@extends('layouts.app')

@section('title', 'My Tasks')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h1 class="mb-4">My Tasks</h1>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Today</h6>
                            <h3>{{ $formattedTodayMinutes }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Yesterday</h6>
                            <h3>{{ $formattedYesterdayMinutes }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h6 class="text-muted">This Week</h6>
                            <h3>{{ $formattedThisWeekMinutes }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Last Week</h6>
                            <h3>{{ $formattedLastWeekMinutes }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <div class="row flex-grow-1">
                        <div class="col-md-3">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search tasks...">
                        </div>
                        <div class="col-md-3">
                            <select id="projectFilter" class="form-select">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex">
                                <button id="filterBtn" class="btn btn-primary me-2">Filter</button>
                                <button id="resetBtn" class="btn btn-secondary">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Active Tasks Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Active Tasks</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-uniform mb-0">
                            <thead>
                                <tr>
                                    <th class="col-key">Key <i class="bi bi-arrow-down"></i></th>
                                    <th class="col-title">Title <i class="bi bi-arrow-down"></i></th>
                                    <th class="col-project">Project</th>
                                    <th class="col-type">Type</th>
                                    <th class="col-status">Status <i class="bi bi-arrow-down"></i></th>
                                    <th class="col-priority">Priority <i class="bi bi-arrow-down"></i></th>
                                    <th class="col-updated">Updated <i class="bi bi-arrow-down"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($openTasks as $task)
                                    <tr>
                                        <td>{{ $task->task_number }}</td>
                                        <td>
                                            <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}">
                                                {{ $task->title }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('projects.show', $task->project) }}">
                                                {{ $task->project->name }}
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
                                        <td>{{ $task->updated_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        @if($openTasks->count() == 0)
                            <div class="text-center py-5">
                                <p>You don't have any active tasks.</p>
                            </div>
                        @endif
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $openTasks->appends(request()->except('closed_page'))->links() }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Closed Tasks Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Closed Tasks</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-uniform mb-0">
                            <thead>
                                <tr>
                                    <th class="col-key">Key</th>
                                    <th class="col-title">Title</th>
                                    <th class="col-project">Project</th>
                                    <th class="col-type">Type</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-priority">Priority</th>
                                    <th class="col-closed">Closed</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($closedTasks as $task)
                                    <tr class="table-light">
                                        <td>{{ $task->task_number }}</td>
                                        <td>
                                            <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}">
                                                {{ $task->title }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('projects.show', $task->project) }}">
                                                {{ $task->project->name }}
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
                                        <td>{{ $task->closed_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        @if($closedTasks->count() == 0)
                            <div class="text-center py-5">
                                <p>You don't have any closed tasks.</p>
                            </div>
                        @endif
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $closedTasks->appends(request()->except('open_page'))->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom CSS for uniform table columns */
.table-uniform {
    table-layout: fixed;
    width: 100%;
}

.table-uniform th,
.table-uniform td {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Column width definitions */
.col-key {
    width: 11%;
}

.col-title {
    width: 35%;
}

.col-project {
    width: 15%;
}

.col-type {
    width: 11%;
}

.col-status {
    width: 11%;
}

.col-priority {
    width: 11%;
}

.col-updated, .col-closed {
    width: 11%;
}

/* Badge styling to make them more consistent */
.badge {
    text-align: center;
    display: inline-block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const searchInput = document.getElementById('searchInput');
    const projectFilter = document.getElementById('projectFilter');
    const filterBtn = document.getElementById('filterBtn');
    const resetBtn = document.getElementById('resetBtn');
    
    // Set initial values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    searchInput.value = urlParams.get('search') || '';
    projectFilter.value = urlParams.get('project_id') || '';
    
    // Filter button click handler
    filterBtn.addEventListener('click', function() {
        applyFilters();
    });
    
    // Reset button click handler
    resetBtn.addEventListener('click', function() {
        searchInput.value = '';
        projectFilter.value = '';
        applyFilters();
    });
    
    // Apply filters function
    function applyFilters() {
        const params = new URLSearchParams();
        
        if (searchInput.value) {
            params.append('search', searchInput.value);
        }
        
        if (projectFilter.value) {
            params.append('project_id', projectFilter.value);
        }
        
        // Preserve sort parameters if present
        if (urlParams.has('sort_by')) {
            params.append('sort_by', urlParams.get('sort_by'));
        }
        
        if (urlParams.has('sort_direction')) {
            params.append('sort_direction', urlParams.get('sort_direction'));
        }
        
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }
    
    // Allow pressing Enter in search field
    searchInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyFilters();
        }
    });
});
</script>
@endsection