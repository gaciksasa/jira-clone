@extends('layouts.app')

@section('title', 'My Vacation Calendar')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        @if(isset($viewingTeam) && $viewingTeam)
            <h2>{{ $team->name }} - Team Time Off Calendar</h2>
            <a href="{{ route('projects.members.index', $team) }}" class="btn btn-outline-primary me-2">
                Back to Team Members
            </a>
        @else
            <h2>My Vacation & Days Off</h2>
        @endif
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestVacationModal">
            Request Days Off
        </button>
    </div>
    
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
    
    <!-- Calendar Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Calendar View</h5>
        </div>
        <div class="card-body">
            <div id="vacation-calendar"></div>
        </div>
    </div>
    
    <!-- My Requests Tab -->
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
                                <td>{{ $request->days_count }}</td>
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
            // Add approved vacation requests
            @foreach($approvedRequests as $request)
            {
                title: '{{ $request->user->name }} - {{ ucfirst($request->type) }}',
                start: '{{ $request->start_date->format("Y-m-d") }}',
                end: '{{ $request->end_date->addDay()->format("Y-m-d") }}',
                className: 'fc-event-{{ $request->type == "vacation" ? "vacation" : ($request->type == "sick_leave" ? "sick" : "personal") }}',
                display: 'block'
            },
            @endforeach
            
            // Add holidays
            @foreach($holidays as $holiday)
            {
                title: '{{ $holiday->name }}',
                start: '{{ $holiday->date->format("Y-m-d") }}',
                className: 'fc-event-holiday',
                display: 'block'
            },
            @endforeach
        ],
        eventDisplay: 'block'
    });
    
    calendar.render();
});
</script>
@endpush