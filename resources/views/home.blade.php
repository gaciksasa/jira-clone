@extends('layouts.app')

@section('title', 'My Tasks')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h2 class="mb-4">My Tasks</h2>
            <div class="card mb-4">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <div class="row flex-grow-1">
                        <div class="col-md-4">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search tasks...">
                        </div>
                        <div class="col-md-6">
                            <select id="projectFilter" class="form-select">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex">
                        <button id="filterBtn" class="btn btn-primary me-2">Filter</button>
                        <button id="resetBtn" class="btn btn-secondary">Reset</button>
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
                                    <th class="col-key">
                                        <a href="{{ route('home', array_merge(request()->except(['open_sort_by', 'open_sort_direction', 'open_page']), [
                                            'open_sort_by' => 'task_number',
                                            'open_sort_direction' => ($openSortField === 'task_number' && $openSortDirection === 'asc') ? 'desc' : 'asc'
                                        ])) }}" class="text-decoration-none text-dark">
                                            Key 
                                            @if($openSortField === 'task_number')
                                                <i class="bi bi-arrow-{{ $openSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="col-title">
                                        <a href="{{ route('home', array_merge(request()->except(['open_sort_by', 'open_sort_direction', 'open_page']), [
                                            'open_sort_by' => 'title',
                                            'open_sort_direction' => ($openSortField === 'title' && $openSortDirection === 'asc') ? 'desc' : 'asc'
                                        ])) }}" class="text-decoration-none text-dark">
                                            Title
                                            @if($openSortField === 'title')
                                                <i class="bi bi-arrow-{{ $openSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="col-project">Project</th>
                                    <th class="col-type">Type</th>
                                    <th class="col-status">
                                        <a href="{{ route('home', array_merge(request()->except(['open_sort_by', 'open_sort_direction', 'open_page']), [
                                            'open_sort_by' => 'task_status_id',
                                            'open_sort_direction' => ($openSortField === 'task_status_id' && $openSortDirection === 'asc') ? 'desc' : 'asc'
                                        ])) }}" class="text-decoration-none text-dark">
                                            Status
                                            @if($openSortField === 'task_status_id')
                                                <i class="bi bi-arrow-{{ $openSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="col-priority">
                                        <a href="{{ route('home', array_merge(request()->except(['open_sort_by', 'open_sort_direction', 'open_page']), [
                                            'open_sort_by' => 'priority_id',
                                            'open_sort_direction' => ($openSortField === 'priority_id' && $openSortDirection === 'asc') ? 'desc' : 'asc'
                                        ])) }}" class="text-decoration-none text-dark">
                                            Priority
                                            @if($openSortField === 'priority_id')
                                                <i class="bi bi-arrow-{{ $openSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="col-updated">
                                        <a href="{{ route('home', array_merge(request()->except(['open_sort_by', 'open_sort_direction', 'open_page']), [
                                            'open_sort_by' => 'updated_at',
                                            'open_sort_direction' => ($openSortField === 'updated_at' && $openSortDirection === 'asc') ? 'desc' : 'asc'
                                        ])) }}" class="text-decoration-none text-dark">
                                            Updated
                                            @if($openSortField === 'updated_at')
                                                <i class="bi bi-arrow-{{ $openSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
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
                                            <span class="badge " style="background-color: {{ $task->type->color ?? '#6c757d' }}">
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
                                        <td>{{ $task->updated_at->format('d.m.Y') }}</td>
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
                            {{ $openTasks->appends(array_merge(request()->except(['open_page', 'closed_page']), [
                                'closed_sort_by' => $closedSortField,
                                'closed_sort_direction' => $closedSortDirection
                            ]))->links() }}
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
                                    <th class="col-key">
                                        <a href="{{ route('home', array_merge(request()->except(['closed_sort_by', 'closed_sort_direction', 'closed_page']), [
                                            'closed_sort_by' => 'task_number',
                                            'closed_sort_direction' => ($closedSortField === 'task_number' && $closedSortDirection === 'asc') ? 'desc' : 'asc'
                                        ])) }}" class="text-decoration-none text-dark">
                                            Key
                                            @if($closedSortField === 'task_number')
                                                <i class="bi bi-arrow-{{ $closedSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="col-title">
                                        <a href="{{ route('home', array_merge(request()->except(['closed_sort_by', 'closed_sort_direction', 'closed_page']), [
                                            'closed_sort_by' => 'title',
                                            'closed_sort_direction' => ($closedSortField === 'title' && $closedSortDirection === 'asc') ? 'desc' : 'asc'
                                        ])) }}" class="text-decoration-none text-dark">
                                            Title
                                            @if($closedSortField === 'title')
                                                <i class="bi bi-arrow-{{ $closedSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="col-project">Project</th>
                                    <th class="col-type">Type</th>
                                    <th class="col-status">
                                        <a href="{{ route('home', array_merge(request()->except(['closed_sort_by', 'closed_sort_direction', 'closed_page']), [
                                            'closed_sort_by' => 'task_status_id',
                                            'closed_sort_direction' => ($closedSortField === 'task_status_id' && $closedSortDirection === 'asc') ? 'desc' : 'asc'
                                        ])) }}" class="text-decoration-none text-dark">
                                            Status
                                            @if($closedSortField === 'task_status_id')
                                                <i class="bi bi-arrow-{{ $closedSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="col-priority">
                                        <a href="{{ route('home', array_merge(request()->except(['closed_sort_by', 'closed_sort_direction', 'closed_page']), [
                                            'closed_sort_by' => 'priority_id',
                                            'closed_sort_direction' => ($closedSortField === 'priority_id' && $closedSortDirection === 'asc') ? 'desc' : 'asc'
                                        ])) }}" class="text-decoration-none text-dark">
                                            Priority
                                            @if($closedSortField === 'priority_id')
                                                <i class="bi bi-arrow-{{ $closedSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="col-closed">
                                        <a href="{{ route('home', array_merge(request()->except(['closed_sort_by', 'closed_sort_direction', 'closed_page']), [
                                            'closed_sort_by' => 'closed_at',
                                            'closed_sort_direction' => ($closedSortField === 'closed_at' && $closedSortDirection === 'asc') ? 'desc' : 'asc'
                                        ])) }}" class="text-decoration-none text-dark">
                                            Closed
                                            @if($closedSortField === 'closed_at')
                                                <i class="bi bi-arrow-{{ $closedSortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
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
                                        <td>{{ $task->closed_at->format('d.m.Y') }}</td>
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
                            {{ $closedTasks->appends(array_merge(request()->except(['open_page', 'closed_page']), [
                                'open_sort_by' => $openSortField,
                                'open_sort_direction' => $openSortDirection
                            ]))->links() }}
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
        
        // Preserve open sort parameters if present
        if (urlParams.has('open_sort_by')) {
            params.append('open_sort_by', urlParams.get('open_sort_by'));
        }
        
        if (urlParams.has('open_sort_direction')) {
            params.append('open_sort_direction', urlParams.get('open_sort_direction'));
        }
        
        // Preserve closed sort parameters if present
        if (urlParams.has('closed_sort_by')) {
            params.append('closed_sort_by', urlParams.get('closed_sort_by'));
        }
        
        if (urlParams.has('closed_sort_direction')) {
            params.append('closed_sort_direction', urlParams.get('closed_sort_direction'));
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