@extends('layouts.app')

@section('title', isset($viewingTeam) && $viewingTeam ? $team->name . ' - Team Time Off Calendar' : 'My Vacation Calendar')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        @if(isset($viewingTeam) && $viewingTeam)
            <h2>{{ $team->name }} - Team Time Off Calendar</h2>
            <div>
                <a href="{{ route('vacation.index') }}" class="btn btn-outline-primary me-2">
                    My Calendar
                </a>
            </div>
        @else
            <h2>My Vacation & Days Off</h2>
            @if(Auth::user()->leadProjects()->count() > 0)
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="teamViewDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Team Calendar
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="teamViewDropdown">
                        @foreach(Auth::user()->leadProjects as $project)
                            <li><a class="dropdown-item" href="{{ route('vacation.index', ['team' => $project->id]) }}">{{ $project->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif
    </div>
    
    <!-- Only show vacation balance card when viewing personal calendar -->
    @if(!isset($viewingTeam) || !$viewingTeam)
    <!-- Vacation Balance Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Vacation Balance {{ date('Y') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="mb-1">{{ $balance->total_days == floor($balance->total_days) ? (int)$balance->total_days : number_format($balance->total_days, 1) }}</h3>
                            <span class="text-muted small">Total Days</span>
                        </div>
                        <div class="col-4">
                            <h3 class="mb-1">{{ $balance->used_days == floor($balance->used_days) ? (int)$balance->used_days : number_format($balance->used_days, 1) }}</h3>
                            <span class="text-muted small">Used Days</span>
                        </div>
                        <div class="col-4">
                            <h3 class="mb-1 {{ $balance->remaining_days < 5 ? 'text-danger' : '' }}">
                                {{ $balance->remaining_days == floor($balance->remaining_days) ? (int)$balance->remaining_days : number_format($balance->remaining_days, 1) }}
                            </h3>
                            <span class="text-muted small">Remaining</span>
                        </div>
                    </div>
                    @if($balance->carryover_days > 0)
                        <div class="text-center mt-3">
                            <span class="badge bg-info">
                                {{ number_format($balance->carryover_days, 1) }} days carried over from previous year
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Upcoming Time Off</h5>
                </div>
                <div class="card-body">
                    @php
                        $upcoming = $requests->where('status', 'approved')
                                           ->where('start_date', '>=', date('Y-m-d'))
                                           ->sortBy('start_date')
                                           ->take(3);
                    @endphp
                    
                    @if($upcoming->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($upcoming as $request)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</strong>
                                        <span class="badge bg-primary ms-2">{{ ucfirst($request->type) }}</span>
                                    </div>
                                    <span>{{number_format( $request->days_count )}} days</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center text-muted my-3">No upcoming time off scheduled</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Team View Summary -->

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> 
        You are viewing the team calendar for <strong>{{ $team->name }}</strong>. This shows time off for all team members.
        Weekends and holidays are not counted as working days.
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Team Time Off Summary</h5>
            <a href="{{ route('projects.members.index', $team) }}" class="btn btn-outline-primary">
                Team Members
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Upcoming Time Off</h6>
                    <ul class="list-group">
                    @php
                        $upcomingTeam = $approvedRequests
                                ->where('start_date', '>=', date('Y-m-d'))
                                ->sortBy('start_date')
                                ->take(5);
                    @endphp
                    
                    @forelse($upcomingTeam as $request)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $request->user->name }}</strong>
                                <span class="badge {{ $request->type == 'vacation' ? 'bg-primary' : ($request->type == 'sick_leave' ? 'bg-danger' : 'bg-warning') }} ms-2">
                                    {{ ucfirst(str_replace('_', ' ', $request->type)) }}
                                </span>
                            </div>
                            <span>{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d') }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">No upcoming team time off</li>
                    @endforelse
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Team Members</h6>
                    <div class="list-group">
                        @foreach($team->members as $member)
                            <a href="{{ route('projects.members.show', [$team, $member]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                {{ $member->name }}
                                @if($member->id == $team->lead_id)
                                    <span class="badge bg-primary">Lead</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Calendar Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ isset($viewingTeam) && $viewingTeam ? 'Team Calendar View' : 'Calendar View' }}</h5>
        </div>
        <div class="card-body">
            <div id="vacation-calendar"></div>
        </div>
    </div>
    
    <!-- Requests Tab -->
    <!-- For Team View -->
    @if(isset($viewingTeam) && $viewingTeam)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pending Team Approval Requests</h5>
                @if($requests->count() > 0)
                    <span class="badge bg-warning">{{ $requests->count() }} Pending</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($requests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Requested On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr>
                                        <td>{{ $request->user->name }}</td>
                                        <td>
                                            <span class="badge {{ $request->type == 'vacation' ? 'bg-primary' : ($request->type == 'sick_leave' ? 'bg-danger' : 'bg-warning') }}">
                                                {{ ucfirst(str_replace('_', ' ', $request->type)) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</td>
                                        <td>{{ format_days($request->days_count) }}</td>
                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('vacation.show', ['vacationRequest' => $request, 'team' => $team->id]) }}" class="btn btn-sm btn-primary">
                                                Review
                                            </a>
                                            
                                            @if($request->approver_id == Auth::id())
                                                <!-- Quick approve/reject buttons if user is the approver -->
                                                <form method="POST" action="{{ route('admin.vacation-requests.approve', $request) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this request?')">
                                                        Approve
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" action="{{ route('admin.vacation-requests.reject', $request) }}" class="d-inline">
                                                    @csrf
                                                    <div class="d-none">
                                                        <input type="text" name="response_comment" value="Request rejected by team lead">
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this request?')">
                                                        Reject
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center">
                        <p class="text-muted mb-0">No pending approval requests from team members</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Regular User View - Personal requests view -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">My Requests</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Approver</th>
                                <th>Status</th>
                                <th>Requested On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                                <tr>
                                    <td>
                                        <span class="badge {{ $request->type == 'vacation' ? 'bg-primary' : ($request->type == 'sick_leave' ? 'bg-danger' : 'bg-warning') }}">
                                            {{ ucfirst(str_replace('_', ' ', $request->type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</td>
                                    <td>{{ format_days($request->days_count) }}</td>
                                    <td>{{ $request->approver->name }}</td>
                                    <td>
                                        @if($request->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($request->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('vacation.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                        
                                        @if($request->status == 'pending')
                                            <form method="POST" action="{{ route('vacation.cancel', $request) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this request?')">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">No vacation requests found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Request Vacation Modal -->
<div class="modal fade" id="requestVacationModal" tabindex="-1" aria-labelledby="requestVacationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestVacationModalLabel">Request Days Off</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('vacation.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="vacation">Vacation</option>
                            <option value="sick_leave">Sick Leave</option>
                            <option value="personal">Personal Leave</option>
                        </select>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="approver_id" class="form-label">Approver</label>
                        <select class="form-select" id="approver_id" name="approver_id" required>
                            @foreach($approvers as $approver)
                                <option value="{{ $approver->id }}">{{ $approver->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comment" class="form-label">Comment (Optional)</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
<style>
    #vacation-calendar {
        height: 500px;
    }
    
    .fc-event-vacation {
        background-color: #0d6efd;
        border-color: #0a58ca;
    }
    
    .fc-event-sick {
        background-color: #dc3545;
        border-color: #b02a37;
    }
    
    .fc-event-personal {
        background-color: #fd7e14;
        border-color: #ca6510;
    }
    
    .fc-event-holiday {
        background-color: #6f42c1;
        border-color: #59359a;
    }

    /* Style for my own vacations */
    .my-vacation {
        border-left: 4px solid #ffa500 !important;
        font-weight: bold;
    }

    /* Style for weekends */
    .fc-day-sat, .fc-day-sun {
        background-color: #f5f5f5;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('vacation-calendar');
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        events: [
            // Add vacation requests - only your own or team's if viewing as team lead
            @foreach($approvedRequests as $request)
                @php
                    // Generate events only for business days in the vacation period
                    $startDate = $request->start_date;
                    $endDate = $request->end_date;
                    $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
                    
                    foreach ($period as $date) {
                        // Skip weekends and holidays
                        if ($date->isWeekend() || \App\Models\Holiday::isHoliday($date)) {
                            continue;
                        }
                @endphp
                {
                    title: '{{ $viewingTeam ? ($request->user->name . " - " . ucfirst($request->type)) : ("My " . ucfirst($request->type)) }}',
                    start: '{{ $date->format("Y-m-d") }}',
                    end: '{{ $date->addDay()->format("Y-m-d") }}',
                    className: 'fc-event-{{ $request->type == "vacation" ? "vacation" : ($request->type == "sick_leave" ? "sick" : "personal") }} {{ $request->user_id == auth()->id() ? "my-vacation" : "" }}',
                    display: 'block'
                },
                @php
                    }
                @endphp
            @endforeach
            
            // Add holidays
            @foreach($holidayEvents as $holiday)
            {
                title: '{{ $holiday['title'] }}',
                start: '{{ $holiday['start'] }}',
                className: '{{ $holiday['className'] }}',
                display: '{{ $holiday['display'] }}'
            },
            @endforeach
        ],
        eventDisplay: 'block'
    });
    
    calendar.render();
});
</script>
@endpush