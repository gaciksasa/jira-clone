@extends('layouts.app')

@section('title', 'User Time Report - ' . $user->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $user->name }} Time Report</h1>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">Print</button>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <form method="GET" action="{{ route('reports.user', $user) }}" class="row g-3">
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
        <div class="card-header h5">Project Time Summary</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Total Time</th>
                            <th>Tasks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projectTotals as $projectId => $projectTotal)
                            <tr>
                                <td>{{ $projectTotal['project']->name }}</td>
                                <td>{{ $projectTotal['formatted_total'] }}</td>
                                <td>{{ count($projectTotal['tasks']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header h5">Detailed Time Breakdown</div>
        <div class="card-body">
            <div class="accordion" id="projectBreakdown">
                @foreach($projectTotals as $projectId => $projectTotal)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $projectId }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $projectId }}" aria-expanded="false" aria-controls="collapse{{ $projectId }}">
                                {{ $projectTotal['project']->name }} - {{ $projectTotal['formatted_total'] }}
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