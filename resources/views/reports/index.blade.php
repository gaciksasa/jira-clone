@extends('layouts.app')

@section('title', 'Time Reports')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Time Reports</h1>
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
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">User Reports</h5>
                </div>
                <div class="card-body">
                    <p>View time reports for your user account or for specific users (admin only).</p>
                    <a href="{{ route('reports.user') }}" class="btn btn-primary">My Time Report</a>
                    
                    @if(Auth::user()->can('manage users'))
                        <div class="mt-4">
                            <h6>User Reports (Admin Only)</h6>
                            <form action="{{ route('reports.user') }}" method="GET" class="row g-3">
                                <div class="col-md-8">
                                    <select name="user" class="form-select">
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
                    @endif
                </div>
            </div>
        </div>
        
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
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const projectSelect = document.getElementById('projectSelect');
        const viewProjectReport = document.getElementById('viewProjectReport');
        const projectReportForm = document.getElementById('projectReportForm');
        
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
    });
</script>
@endpush
@endsection