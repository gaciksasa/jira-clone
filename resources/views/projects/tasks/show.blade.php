@extends('layouts.app')

@section('title', $task->task_number . ' - ' . $task->title)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $task->task_number }}: {{ $task->title }}</h1>
            <p class="text-muted mb-0">{{ $project->name }} | {{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Project</a>
            <a href="{{ route('projects.board', $project) }}" class="btn btn-outline-primary">Board</a>
            <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">Description</div>
                <div class="card-body">
                    {!! nl2br(e($task->description)) ?: '<em>No description provided</em>' !!}
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Comments</span>
                </div>
                <div class="card-body">
                    @if($task->comments->count() > 0)
                        @foreach($task->comments as $comment)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>{{ $comment->user->name }}</strong>
                                    </div>
                                    <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                                <div>
                                    {!! nl2br(e($comment->content)) !!}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-center">No comments yet.</p>
                    @endif
                    
                    <form method="POST" action="{{ route('projects.tasks.comments.store', [$project, $task]) }}" class="mt-4">
                        @csrf
                        <div class="mb-3">
                            <label for="content" class="form-label">Add Comment</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="3" required></textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">Add Comment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">Details</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Type:</span>
                            <span class="badge" style="background-color: {{ $task->type->color ?? '#6c757d' }}">
                                {{ $task->type->name }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Status:</span>
                            <span class="badge" style="background-color: {{ $task->status->color ?? '#6c757d' }}">
                                {{ $task->status->name }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Priority:</span>
                            <span class="badge" style="background-color: {{ $task->priority->color ?? '#6c757d' }}">
                                {{ $task->priority->name }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Reporter:</span>
                            <span>{{ $task->reporter->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Assignee:</span>
                            <span>{{ $task->assignee->name ?? 'Unassigned' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Sprint:</span>
                            <span>{{ $task->sprint->name ?? 'Backlog' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Story Points:</span>
                            <span>{{ $task->story_points ?? 'Not specified' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Created:</span>
                            <span>{{ $task->created_at->format('d.m.Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Updated:</span>
                            <span>{{ $task->updated_at->format('d.m.Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            @if($task->labels->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">Labels</div>
                    <div class="card-body">
                        @foreach($task->labels as $label)
                            <span class="badge bg-secondary mb-1">{{ $label->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!--<div class="card">
                <div class="card-header">Actions</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($task->closed_at)
                            <div class="alert alert-secondary">
                                <i class="bi bi-info-circle"></i> This task was closed on {{ $task->closed_at->format('d.m.Y') }}
                            </div>
                            <form method="POST" action="{{ route('projects.tasks.reopen', [$project, $task]) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success w-100">Reopen Task</button>
                            </form>
                        @else
                            <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="btn btn-primary">Edit Task</a>
                            
                            <form method="POST" action="{{ route('projects.tasks.close', [$project, $task]) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-secondary w-100">Close Task</button>
                            </form>
                        @endif
                        
                        <form method="POST" action="{{ route('projects.tasks.destroy', [$project, $task]) }}" onsubmit="return confirm('Are you sure you want to delete this task? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">Delete Task</button>
                        </form>
                    </div>
                </div>
            </div>-->
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Time Tracking</span>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#logTimeModal">
                    Log Time
                </button>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <strong>Total Time Spent:</strong>
                    <span>{{ $task->formattedTotalTime() }}</span>
                </div>
                
                @if($task->timeLogs->count() > 0)
                    <h6>Recent Time Entries</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Description</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($task->timeLogs()->with('user')->latest()->take(5)->get() as $log)
                                    <tr>
                                        <td>{{ $log->user->name }}</td>
                                        <td>{{ $log->work_date->format('d.m.Y') }}</td>
                                        <td>{{ $log->formattedTime() }}</td>
                                        <td>{{ $log->description ?? '-' }}</td>
                                        <td>
                                            @if($log->user_id === Auth::id() || Auth::user()->hasRole('admin'))
                                                <form method="POST" action="{{ route('projects.tasks.time-logs.destroy', [$project, $task, $log]) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this time log?');">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($task->timeLogs->count() > 5)
                        <div class="text-center mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#allTimeLogsModal">
                                View All Time Logs
                            </button>
                        </div>
                    @endif
                @else
                    <p class="text-center text-muted">No time has been logged for this task yet.</p>
                @endif
            </div>
        </div>

        <!-- Log Time Modal -->
        <div class="modal fade" id="logTimeModal" tabindex="-1" aria-labelledby="logTimeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="logTimeModalLabel">Log Time for {{ $task->task_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('projects.tasks.time-logs.store', [$project, $task]) }}">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="time-spent" class="form-label">Time Spent</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control" id="hours" min="0" value="0">
                                            <span class="input-group-text">h</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control" id="minutes" min="0" max="59" value="0">
                                            <span class="input-group-text">m</span>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="minutes" id="total-minutes" value="0">
                            </div>
                            
                            <div class="mb-3">
                                <label for="work_date" class="form-label">Date of Work</label>
                                <input type="date" class="form-control" id="work_date" name="work_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description (optional)</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="log-time-submit">Log Time</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- All Time Logs Modal -->
        @if($task->timeLogs->count() > 5)
        <div class="modal fade" id="allTimeLogsModal" tabindex="-1" aria-labelledby="allTimeLogsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="allTimeLogsModalLabel">All Time Logs for {{ $task->task_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Description</th>
                                        <th>Logged On</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($task->timeLogs()->with('user')->latest()->get() as $log)
                                        <tr>
                                            <td>{{ $log->user->name }}</td>
                                            <td>{{ $log->work_date->format('d.m.Y') }}</td>
                                            <td>{{ $log->formattedTime() }}</td>
                                            <td>{{ $log->description ?? '-' }}</td>
                                            <td>{{ $log->created_at->format('d.m.Y H:i') }}</td>
                                            <td>
                                                @if($log->user_id === Auth::id() || Auth::user()->hasRole('admin'))
                                                    <form method="POST" action="{{ route('projects.tasks.time-logs.destroy', [$project, $task, $log]) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this time log?');">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Calculate total minutes when hours or minutes change
                const hoursInput = document.getElementById('hours');
                const minutesInput = document.getElementById('minutes');
                const totalMinutesInput = document.getElementById('total-minutes');
                const logTimeSubmit = document.getElementById('log-time-submit');
                
                function updateTotalMinutes() {
                    const hours = parseInt(hoursInput.value) || 0;
                    const minutes = parseInt(minutesInput.value) || 0;
                    const totalMinutes = (hours * 60) + minutes;
                    
                    totalMinutesInput.value = totalMinutes;
                    
                    // Disable submit button if total time is 0
                    if (totalMinutes <= 0) {
                        logTimeSubmit.disabled = true;
                    } else {
                        logTimeSubmit.disabled = false;
                    }
                }
                
                hoursInput.addEventListener('input', updateTotalMinutes);
                minutesInput.addEventListener('input', updateTotalMinutes);
                
                // Initialize
                updateTotalMinutes();
            });
        </script>
        @endpush
    </div>
</div>
@endsection