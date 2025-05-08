@extends('layouts.app')

@section('title', 'Project Time Report - ' . $project->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Project Time Report</h1>
            <p class="text-muted mb-0">{{ $project->name }} | {{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Back to Project</a>
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">Print Report</button>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.reports.project', $project) }}" class="row g-3">
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
                <strong>Total Hours:</strong> {{ $formattedProjectTotal }}
            </div>
            <div>
                <strong>Period:</strong> {{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">Member Time Summary</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Total Time</th>
                            <th>Tasks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($userTotals as $userId => $userTotal)
                            <tr>
                                <td>{{ $userTotal['user']->name }}</td>
                                <td>{{ $userTotal['formatted_total'] }}</td>
                                <td>{{ count($userTotal['tasks']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">Detailed Time Breakdown</div>
        <div class="card-body">
            <div class="accordion" id="userBreakdown">
                @foreach($userTotals as $userId => $userTotal)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $userId }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $userId }}" aria-expanded="false" aria-controls="collapse{{ $userId }}">
                                {{ $userTotal['user']->name }} - {{ $userTotal['formatted_total'] }}
                            </button>
                        </h2>
                        <div id="collapse{{ $userId }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $userId }}" data-bs-parent="#userBreakdown">
                            <div class="accordion-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Task</th>
                                            <th>Total Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($userTotal['tasks'] as $taskId => $taskData)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('projects.tasks.show', [$project, $taskData['task']]) }}">
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