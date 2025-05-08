@extends('layouts.app')

@section('title', 'My Time Report')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Time Report</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">Print Report</button>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5>This Week</h5>
                            <h2>{{ \App\Http\Controllers\TimesheetController::formatMinutes($thisWeekTotal) }}</h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 text-center mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5>This Month</h5>
                            <h2>{{ \App\Http\Controllers\TimesheetController::formatMinutes($thisMonthTotal) }}</h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 text-center mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5>This Year</h5>
                            <h2>{{ \App\Http\Controllers\TimesheetController::formatMinutes($thisYearTotal) }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Report Section (Previously at /reports/user) -->
    <div class="card mb-4">
        <div class="card-header">
            <form method="GET" action="{{ route('reports.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="alert alert-info">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>Total Hours:</strong> {{ $formattedUserTotal }}
            </div>
            <div>
                <strong>Period:</strong> {{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header h5">Time Breakdown</div>
        <div class="card-body">
            <div class="accordion" id="projectBreakdown">
                @foreach($projectTotals as $projectId => $projectTotal)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $projectId }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $projectId }}" aria-expanded="false" aria-controls="collapse{{ $projectId }}">
                                {{ $projectTotal['project']->name }} <span class="badge bg-primary mx-2"> {{ count($projectTotal['tasks']) }} tasks </span> - {{ $projectTotal['formatted_total'] }}
                            </button>
                        </h2>
                        <div id="collapse{{ $projectId }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $projectId }}" data-bs-parent="#projectBreakdown">
                            <div class="accordion-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Task</th>
                                            <th>Total Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($projectTotal['tasks'] as $taskId => $taskData)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('projects.tasks.show', [$projectTotal['project'], $taskData['task']]) }}">
                                                        {{ $taskData['task']->task_number }} - {{ $taskData['task']->title }}
                                                    </a>
                                                </td>
                                                <td>{{ $taskData['formatted_total'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    @if(Auth::user()->can('manage users'))
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Project Reports</h5>
                </div>
                <div class="card-body">
                    <p>View time reports for projects you have access to.</p>
                    <form action="{{ route('reports.project', ['project' => '__PROJECT_ID__']) }}" method="GET" class="row g-3" id="projectReportForm">
                        <div class="col-md-8">
                            <select id="projectSelect" class="form-select">
                                <option value="">Select Project</option>
                                @foreach(Auth::user()->projects()->orderBy('name')->get() as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary" id="viewProjectReport">View Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">User Reports</h5>
                </div>
                <div class="card-body">
                    <p>View time reports for specific users.</p>
                    <form action="{{ route('reports.index') }}" method="GET" class="row g-3">
                        <div class="col-md-8">
                            <select name="user_id" class="form-select">
                                <option value="">Select User</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">View Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const projectSelect = document.getElementById('projectSelect');
        const viewProjectReport = document.getElementById('viewProjectReport');
        const projectReportForm = document.getElementById('projectReportForm');
        
        if (viewProjectReport) {
            viewProjectReport.addEventListener('click', function() {
                const projectId = projectSelect.value;
                if (projectId) {
                    // Replace the placeholder in the form action with the actual project ID
                    const formAction = projectReportForm.getAttribute('action').replace('__PROJECT_ID__', projectId);
                    
                    // Navigate to the project report
                    window.location.href = formAction;
                } else {
                    alert('Please select a project');
                }
            });
        }
    });
</script>
@endpush
@endsection