@extends('layouts.app')

@section('title', $task->task_number . ' - ' . $task->title)

@section('content')
<div class="container">
    @if($task->parent_id)
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle"></i> 
            This is a subtask of 
            <a href="{{ route('projects.tasks.show', [$project, $task->parent]) }}">{{ $task->parent->task_number }}: {{ $task->parent->title }}</a>.
            <form method="POST" action="{{ route('projects.tasks.detach', [$project, $task]) }}" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-light ms-3">Remove as Subtask</button>
            </form>
        </div>
    @endif
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $task->task_number }}: {{ $task->title }}</h2>
            <p class="text-muted mb-0">{{ $project->name }} | {{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Project</a>
            <a href="{{ route('projects.board', $project) }}" class="btn btn-outline-primary">Board</a>
            <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="btn btn-outline-primary">Edit</a>
            
            @if(!$task->closed_at && $task->status->name == 'Done')
                <button type="button" class="btn btn-success" onclick="closeTaskForm.submit()">Close</button>
                <form name="closeTaskForm" method="POST" action="{{ route('projects.tasks.close', [$project, $task]) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                </form>
            @endif
            
            @if($task->closed_at)
                <button type="button" class="btn btn-danger" onclick="reopenTaskForm.submit()">Reopen</button>
                <form name="reopenTaskForm" method="POST" action="{{ route('projects.tasks.reopen', [$project, $task]) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                </form>
            @endif
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header h5">Description</div>
                <div class="card-body">
                    {!! $task->description ?: '<em>No description provided</em>' !!}
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Attachments</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadAttachmentModal">
                        Add Attachment
                    </button>
                </div>
                <div class="card-body">
                    @if($task->attachments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>File</th>
                                        <th>Size</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($task->attachments as $attachment)
                                        <tr>
                                            <td>
                                                <i class="bi bi-file-earmark"></i>
                                                {{ $attachment->filename }}
                                            </td>
                                            <td>{{ round($attachment->file_size / 1024, 2) }} KB</td>
                                            <td>{{ $attachment->user->name }}</td>
                                            <td>{{ $attachment->created_at->format('d.m.Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('projects.tasks.attachments.download', [$project, $task, $attachment]) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                
                                                @if($attachment->user_id === Auth::id() || $project->lead_id === Auth::id() || Auth::user()->hasRole('admin'))
                                                    <form method="POST" action="{{ route('projects.tasks.attachments.destroy', [$project, $task, $attachment]) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this attachment?');">
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
                    @else
                        <p class="text-center">No attachments yet.</p>
                    @endif
                </div>
            </div>

            @if(!$task->isSubtask())
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Subtasks</h5>
                        <div>
                            <span class="text-muted me-2" id="subtask-progress">
                                {{ $task->completedSubtasksCount() }}/{{ $task->subtasks->count() }} completed
                            </span>
                            <a href="{{ route('projects.tasks.create', $project) }}?parent_id={{ $task->id }}" class="btn btn-sm btn-outline-primary me-2">
                                Create Subtask
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignSubtaskModal">
                                Assign Subtask
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($task->subtasks->count() > 0)
                            <div class="progress mb-3">
                                <div class="progress-bar" role="progressbar" style="width: {{ $task->subtaskCompletionPercentage() }}%;" 
                                    aria-valuenow="{{ $task->subtaskCompletionPercentage() }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $task->subtaskCompletionPercentage() }}%
                                </div>
                            </div>
                            
                            <div class="subtasks-list" id="subtasksList">
                                @foreach($task->subtasks as $subtask)
                                    <div class="card mb-2 subtask-item" data-subtask-id="{{ $subtask->id }}">
                                        <div class="card-body p-2">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1 ms-3 {{ $subtask->closed_at ? 'text-decoration-line-through text-muted' : '' }}">
                                                    <div class="d-flex justify-content-between">
                                                        <h6 class="mb-0">
                                                            <a href="{{ route('projects.tasks.show', [$project, $subtask]) }}">
                                                                {{ $subtask->task_number }}: {{ $subtask->title }}
                                                            </a>
                                                        </h6>
                                                    </div>
                                                    @if($subtask->assignee)
                                                        <small class="text-muted">Assigned to: {{ $subtask->assignee->name }}</small>
                                                    @endif
                                                    <div class="mt-1">
                                                        <span class="badge" style="background-color: {{ $subtask->status->color ?? '#6c757d' }}">
                                                            {{ $subtask->status->name }}
                                                        </span>
                                                        <span class="badge" style="background-color: {{ $subtask->priority->color ?? '#6c757d' }}">
                                                            {{ $subtask->priority->name }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-center">No subtasks yet. Click 'Add Subtask' to create one.</p>
                        @endif
                    </div>
                </div>
                @else
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Parent Task</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-0">
                            <a href="{{ route('projects.tasks.show', [$project, $task->parent]) }}">
                                {{ $task->parent->task_number }}: {{ $task->parent->title }}
                            </a>
                        </h6>
                    </div>
                </div>
                @endif
            
            <div class="card mb-4">
                <div class="card-header h5">Comments</div>
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
                                    {!! $comment->content !!}
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
                            <x-tinymce-editor 
                                id="content" 
                                name="content" 
                                placeholder="Add your comment here..." 
                                :value="old('content')"
                            />
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
                <div class="card-header h5">Details</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Project:</span>
                            <span>
                                {{ $task->project->name }}
                            </span>
                        </li>
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
                        <!--<li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Sprint:</span>
                            <span>{{ $task->sprint->name ?? 'Backlog' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Story Points:</span>
                            <span>{{ $task->story_points ?? 'Not specified' }}</span>
                        </li>-->
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Created:</span>
                            <span>{{ $task->created_at->format('d.m.Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Updated:</span>
                            <span>{{ $task->updated_at->format('d.m.Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Total Time Spent:</span>
                            <span>{{ $task->formattedTotalTime() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            @if($task->labels->count() > 0)
                <div class="card mb-4">
                    <div class="card-header h5">Labels</div>
                    <div class="card-body">
                        @foreach($task->labels as $label)
                            <a href="{{ route('projects.tasks.by-label', [$project, $label]) }}" class="text-decoration-none">
                                <span class="badge mb-1" style="background-color: {{ $label->color }}">{{ $label->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>  
    </div>
    <div class="card p-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Time Tracking</h5>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#logTimeModal">
                Log Time
            </button>
        </div>
        <div class="card-body">
            @if($task->timeLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Description</th>
                                <th>Time</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($task->timeLogs()->with('user')->latest()->get() as $log)
                                <tr>
                                    <td>{{ $log->work_date->format('d.m.Y') }}</td>
                                    <td>{{ $log->user->name }}</td>
                                    <td>{{ $log->description ?? '-' }}</td>
                                    <td>{{ $log->formattedTime() }}</td>
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

    <!-- Upload Attachment Modal -->
    <div class="modal fade" id="uploadAttachmentModal" tabindex="-1" aria-labelledby="uploadAttachmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadAttachmentModalLabel">Upload Attachment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('projects.tasks.attachments.store', [$project, $task]) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="attachment" class="form-label">File</label>
                            <input type="file" class="form-control @error('attachment') is-invalid @enderror" id="attachment" name="attachment" required>
                            <div class="form-text">Maximum file size: 10MB</div>
                            @error('attachment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

<!-- Add Subtask Modal -->
<div class="modal fade" id="addSubtaskModal" tabindex="-1" aria-labelledby="addSubtaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubtaskModalLabel">Add Subtask</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addSubtaskForm" action="{{ route('projects.tasks.subtasks.store', [$project, $task]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subtask-title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="subtask-title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="subtask-description" class="form-label">Description (optional)</label>
                        <textarea class="form-control" id="subtask-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="subtask-assignee" class="form-label">Assignee (optional)</label>
                        <select class="form-select" id="subtask-assignee" name="assignee_id">
                            <option value="">Unassigned</option>
                            @foreach($project->members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Subtask</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Subtask Modal -->
<div class="modal fade" id="assignSubtaskModal" tabindex="-1" aria-labelledby="assignSubtaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignSubtaskModalLabel">Assign Existing Task as Subtask</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="subtask_assignment" value="1">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subtask_id" class="form-label">Select Task</label>
                        <select class="form-select" id="subtask_id" name="subtask_id" required>
                            <option value="">Select a task...</option>
                            @foreach($project->tasks()->whereNull('parent_id')->where('id', '!=', $task->id)->get() as $potentialSubtask)
                                <option value="{{ $potentialSubtask->id }}">
                                    {{ $potentialSubtask->task_number }}: {{ Str::limit($potentialSubtask->title, 50) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subtask Modal -->
<div class="modal fade" id="editSubtaskModal" tabindex="-1" aria-labelledby="editSubtaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubtaskModalLabel">Edit Subtask</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSubtaskForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-subtask-title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="edit-subtask-title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-subtask-description" class="form-label">Description (optional)</label>
                        <textarea class="form-control" id="edit-subtask-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-subtask-assignee" class="form-label">Assignee (optional)</label>
                        <select class="form-select" id="edit-subtask-assignee" name="assignee_id">
                            <option value="">Unassigned</option>
                            @foreach($project->members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Subtask</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        // Make subtasks sortable
        const subtasksList = document.getElementById('subtasksList');
        if (subtasksList) {
            new Sortable(subtasksList, {
                animation: 150,
                handle: '.card-body',
                onEnd: function(evt) {
                    // Get all subtask IDs in the new order
                    const subtaskIds = Array.from(subtasksList.querySelectorAll('.subtask-item')).map(item => {
                        return item.dataset.subtaskId;
                    });
                    
                    // Send the new order to the server
                    fetch("{{ route('projects.tasks.subtasks.reorder', [$project, $task]) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            subtasks: subtaskIds
                        })
                    })
                    .then(response => response.json())
                    .catch(error => console.error('Error:', error));
                }
            });
        }
        
        // Handle subtask form submission via AJAX
        document.getElementById('addSubtaskForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal
                    bootstrap.Modal.getInstance(document.getElementById('addSubtaskModal')).hide();
                    
                    // Refresh the page to show the new subtask
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        });
        
        // Load subtask data into edit modal
        document.querySelectorAll('.edit-subtask-btn').forEach(button => {
            button.addEventListener('click', function() {
                const subtaskId = this.dataset.subtaskId;
                const subtaskItem = document.querySelector(`.subtask-item[data-subtask-id="${subtaskId}"]`);
                
                // Extract subtask data from the DOM (or you could fetch it from the server)
                const title = subtaskItem.querySelector('h6').textContent.trim();
                const description = subtaskItem.querySelector('p.small') ? 
                    subtaskItem.querySelector('p.small').textContent.trim() : '';
                
                // Set form action URL
                const form = document.getElementById('editSubtaskForm');
                form.action = `{{ route('projects.tasks.subtasks.update', [$project, $task, '__SUBTASK_ID__']) }}`.replace('__SUBTASK_ID__', subtaskId);
                
                // Fill form fields
                document.getElementById('edit-subtask-title').value = title;
                document.getElementById('edit-subtask-description').value = description;
                
                // Set assignee if present
                const assigneeText = subtaskItem.querySelector('small') ? 
                    subtaskItem.querySelector('small').textContent : '';
                    
                if (assigneeText) {
                    const assigneeName = assigneeText.replace('Assigned to: ', '').trim();
                    const assigneeSelect = document.getElementById('edit-subtask-assignee');
                    
                    // Find and select the matching option
                    Array.from(assigneeSelect.options).forEach(option => {
                        if (option.textContent === assigneeName) {
                            option.selected = true;
                        }
                    });
                }
            });
        });
        
        // Handle edit subtask form submission
        document.getElementById('editSubtaskForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal
                    bootstrap.Modal.getInstance(document.getElementById('editSubtaskModal')).hide();
                    
                    // Refresh the page to show the updated subtask
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        });
        
        // Handle subtask deletion
        document.querySelectorAll('.delete-subtask-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this subtask?')) {
                    const subtaskId = this.dataset.subtaskId;
                    
                    fetch(`{{ route('projects.tasks.subtasks.destroy', [$project, $task, '__SUBTASK_ID__']) }}`.replace('__SUBTASK_ID__', subtaskId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the subtask item from the DOM
                            const subtaskItem = document.querySelector(`.subtask-item[data-subtask-id="${subtaskId}"]`);
                            subtaskItem.remove();
                            
                            // Refresh the page to update counts
                            window.location.reload();
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        // Make subtasks sortable
        const subtasksList = document.getElementById('subtasksList');
        if (subtasksList) {
            new Sortable(subtasksList, {
                animation: 150,
                handle: '.card-body',
                onEnd: function(evt) {
                    // Get all subtask IDs in the new order
                    const subtaskIds = Array.from(subtasksList.querySelectorAll('.subtask-item')).map(item => {
                        return item.dataset.subtaskId;
                    });
                    
                    // Send the new order to the server
                    fetch("{{ route('projects.tasks.subtasks.reorder', [$project, $task]) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            subtasks: subtaskIds
                        })
                    })
                    .then(response => response.json())
                    .catch(error => console.error('Error:', error));
                }
            });
        }
    });
</script>
@endpush
@endsection