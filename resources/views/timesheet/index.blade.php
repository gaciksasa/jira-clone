@extends('layouts.app')

@section('title', 'My Timesheet')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">My Timesheet</h2>

    <!-- Time tracking cards -->
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

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4>Month</h4>
        <form method="GET" action="{{ route('timesheet.index') }}" class="d-flex align-items-center">
            <select class="form-select me-2" name="month" id="month_selector" onchange="this.form.submit()">
                @foreach($availableMonths as $availableMonth)
                    <option value="{{ $availableMonth['month'] }}" 
                            {{ ($month == $availableMonth['month'] && $year == $availableMonth['year']) ? 'selected' : '' }}>
                        {{ $availableMonth['name'] }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="year" value="{{ $year }}">
            <!--<button type="submit" class="btn btn-primary">View</button>-->
        </form>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Time entries</h5>
            <div class="d-flex align-items-center">
                <span class="me-2">Monthly Total:</span>
                <span class="badge bg-primary" id="monthly-total">{{ $formattedMonthlyTotal }}</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="timesheet-container">
                <table class="table table-bordered timesheet-table mb-0">
                    <thead>
                        <tr class="table-header">
                            <th class="task-column">Task</th>
                            @foreach($days as $day)
                                <th class="day-column{{ $day->isWeekend() ? ' bg-light' : '' }}{{ $day->isToday() ? ' bg-info text-white' : '' }}">
                                    <div>{{ $day->format('D') }}</div>
                                    <div>{{ $day->format('j') }}</div>
                                </th>
                            @endforeach
                            <th class="total-column">Total</th>
                        </tr>
                        <tr class="daily-totals">
                            <th class="task-column">Daily Total</th>
                            @foreach($days as $day)
                                @php $date = $day->format('Y-m-d'); @endphp
                                <td class="text-center daily-total{{ $day->isWeekend() ? ' bg-light' : '' }}{{ $day->isToday() ? ' bg-info text-white' : '' }}" 
                                    data-date="{{ $date }}" id="daily-total-{{ $date }}">
                                    {{ \App\Http\Controllers\TimesheetController::formatMinutes($dailyTotals[$date] ?? 0) }}
                                </td>
                            @endforeach
                            <th class="total-column bg-light text-center" id="grand-total">
                                {{ $formattedMonthlyTotal }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr data-task-id="{{ $task->id }}">
                                <td class="task-info">
                                    <div>
                                        <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}">
                                            <strong>{{ $task->task_number }}</strong>: {{ Str::limit($task->title, 60) }}
                                        </a>
                                    </div>
                                    <div class="task-meta">
                                        <span class="badge" style="background-color: {{ $task->type->color ?? '#6c757d' }}">
                                            {{ $task->type->name }}
                                        </span>
                                        <span class="badge" style="background-color: {{ $task->priority->color ?? '#6c757d' }}">
                                            {{ $task->priority->name }}
                                        </span>
                                        <span class="badge" style="background-color: {{ $task->status->color ?? '#6c757d' }}">
                                            {{ $task->status->name }}
                                        </span>
                                    </div>
                                </td>
                                @foreach($days as $day)
                                    @php 
                                        $date = $day->format('Y-m-d');
                                        $minutes = $taskLogsMatrix[$task->id][$date] ?? 0;
                                    @endphp
                                    <td class="time-cell{{ $day->isWeekend() ? ' bg-light' : '' }}" 
                                        data-task-id="{{ $task->id }}" 
                                        data-date="{{ $date }}">
                                        <input type="text" class="form-control time-input" 
                                               value="{{ $minutes > 0 ? \App\Http\Controllers\TimesheetController::formatMinutes($minutes) : '' }}" 
                                               placeholder="0h 0m"
                                               data-minutes="{{ $minutes }}">
                                    </td>
                                @endforeach
                                <td class="task-total text-center" id="task-total-{{ $task->id }}">
                                    {{ \App\Http\Controllers\TimesheetController::formatMinutes($taskTotals[$task->id] ?? 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($days) + 2 }}" class="text-center py-4">
                                    No tasks found. Start by logging time to tasks to see them here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Add Task to Timesheet</h5>
        </div>
        <div class="card-body">
            <p>To add a task to this timesheet, first log time to it from the task page or by using the quick log option below.</p>
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickLogModal">Quick Log Time</a>
        </div>
    </div>
</div>

<!-- Quick Log Modal -->
<div class="modal fade" id="quickLogModal" tabindex="-1" aria-labelledby="quickLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickLogModalLabel">Quick Log Time</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickLogForm">
                    <div class="mb-3">
                        <label for="projectSelect" class="form-label">Project</label>
                        <select class="form-select" id="projectSelect" required>
                            <option value="">Select Project</option>
                            @foreach(Auth::user()->projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taskSelect" class="form-label">Task</label>
                        <select class="form-select" id="taskSelect" disabled required>
                            <option value="">Select Task</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="workDate" class="form-label">Date</label>
                        <input type="date" class="form-control" id="workDate" required value="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="mb-3">
                        <label for="timeSpent" class="form-label">Time Spent</label>
                        <div class="row">
                            <div class="col-6">
                                <div class="input-group">
                                    <input type="number" class="form-control" id="hours" min="0" value="0">
                                    <span class="input-group-text">h</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group">
                                    <input type="number" class="form-control" id="minutes" min="0" max="59" value="0">
                                    <span class="input-group-text">m</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (optional)</label>
                        <textarea class="form-control" id="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="quickLogSubmit">Log Time</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timesheet-container {
        overflow-x: auto;
        position: relative;
        max-width: 100%;
    }
    
    .timesheet-table {
        min-width: 100%;
        border-collapse: collapse;
    }
    
    .table-header {
        position: sticky;
        top: 0;
        z-index: 20;
    }
    
    .task-column {
        width: 300px;
        min-width: 300px;
        max-width: 300px;
        position: sticky;
        left: 0;
        background-color: white !important;
        z-index: 10;
        border-right: 2px solid #dee2e6;
    }
    
    .task-info {
        width: 300px;
        min-width: 300px;
        max-width: 300px;
        word-wrap: break-word;
        background-color: white !important;
        position: sticky;
        left: 0;
        z-index: 10;
        border-right: 2px solid #dee2e6;
    }
    
    .day-column {
        min-width: 80px;
        text-align: center;
    }
    
    .total-column {
        min-width: 100px;
        text-align: center;
        position: sticky;
        right: 0;
        background-color: white !important;
        z-index: 10;
        border-left: 2px solid #dee2e6;
    }
    
    .task-total {
        min-width: 100px;
        position: sticky;
        right: 0;
        background-color: white !important;
        z-index: 10;
        border-left: 2px solid #dee2e6;
    }
    
    .time-cell {
        padding: 0.25rem !important;
        position: relative;
    }
    
    .time-input {
        min-width: 70px;
        height: calc(1.5em + 0.5rem + 2px);
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        text-align: center;
    }
    
    .daily-totals th {
        position: sticky;
        top: 38px;
        z-index: 20;
    }
    
    .task-meta {
        margin: 5px 0;
    }
    
    .loading {
        opacity: 0.5;
        pointer-events: none;
    }
    
    .time-input:focus {
        z-index: 100;
        position: relative;
    }

    @media (max-width: 768px) {
        .task-column, .task-info {
            width: 200px;
            min-width: 200px;
            max-width: 200px;
        }
        
        .day-column {
            min-width: 60px;
        }
        
        .total-column, .task-total {
            min-width: 80px;
        }
        
        .time-input {
            min-width: 50px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Process time input format
    function parseTimeInput(value) {
        // Empty input is 0 minutes
        if (!value || value.trim() === '' || value === '0' || value === '0m' || value === '0h' || value === '0h 0m') {
            return 0;
        }
        
        // Check for direct minutes input (e.g. "45m" or "45")
        if (value.match(/^(\d+)m?$/)) {
            return parseInt(value.replace('m', ''));
        }
        
        // Handle hours input (e.g. "1h" or "1.5h")
        if (value.match(/^(\d+(\.\d+)?)h$/)) {
            const hours = parseFloat(value.replace('h', ''));
            return Math.round(hours * 60);
        }
        
        // Handle combined format (e.g. "1h 30m")
        const combined = value.match(/^(\d+)h\s+(\d+)m$/);
        if (combined) {
            const hours = parseInt(combined[1]);
            const minutes = parseInt(combined[2]);
            return (hours * 60) + minutes;
        }
        
        // Handle decimal hours (e.g. "1.5")
        if (value.match(/^\d+\.\d+$/)) {
            const hours = parseFloat(value);
            return Math.round(hours * 60);
        }
        
        // Default to 0 if format is not recognized
        return 0;
    }
    
    // Format minutes for display
    function formatMinutes(minutes) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        
        let result = '';
        if (hours > 0) {
            result += hours + 'h ';
        }
        if (mins > 0 || hours === 0) {
            result += mins + 'm';
        }
        
        return result.trim();
    }
    
    // Handle time input changes
    const timeInputs = document.querySelectorAll('.time-input');
    timeInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const taskId = this.closest('td').dataset.taskId;
            const date = this.closest('td').dataset.date;
            const minutes = parseTimeInput(this.value);
            
            // Update displayed value to standard format
            this.value = minutes > 0 ? formatMinutes(minutes) : '';
            
            // Store the raw minutes value for calculations
            this.dataset.minutes = minutes;
            
            // Update the server via AJAX
            updateTimeLog(taskId, date, minutes);
        });
        
        // Support for Enter key
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.blur();
            }
        });
    });
    
    // Update time log via AJAX
    function updateTimeLog(taskId, date, minutes) {
        const cell = document.querySelector(`td[data-task-id="${taskId}"][data-date="${date}"]`);
        const input = cell.querySelector('.time-input');
        
        // Add loading indicator
        cell.classList.add('loading');
        
        fetch('{{ route('timesheet.update') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                task_id: taskId,
                date: date,
                minutes: minutes
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update the input with formatted time
                input.value = minutes > 0 ? formatMinutes(minutes) : '';
                input.dataset.minutes = minutes;
                
                // Update task total
                const taskTotal = document.getElementById(`task-total-${taskId}`);
                if (taskTotal) {
                    taskTotal.textContent = data.formattedTaskTotal;
                }
                
                // Update daily total
                const dailyTotal = document.getElementById(`daily-total-${date}`);
                if (dailyTotal) {
                    dailyTotal.textContent = data.formattedDailyTotal;
                }
                
                // Update monthly total
                const monthlyTotal = document.getElementById('monthly-total');
                const grandTotal = document.getElementById('grand-total');
                if (monthlyTotal) {
                    monthlyTotal.textContent = data.formattedMonthlyTotal;
                }
                if (grandTotal) {
                    grandTotal.textContent = data.formattedMonthlyTotal;
                }
                
                // Manually recalculate and update the Daily Total row in the table header
                // This ensures the total is updated even if the server doesn't explicitly return it
                recalculateDailyTotals();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update time log. Please try again.');
        })
        .finally(() => {
            // Remove loading indicator
            cell.classList.remove('loading');
        });
    }
    
    // Function to manually recalculate daily totals
    function recalculateDailyTotals() {
        // Get all days
        const dailyTotalCells = document.querySelectorAll('[id^="daily-total-"]');
        
        dailyTotalCells.forEach(cell => {
            const date = cell.id.replace('daily-total-', '');
            
            // Get all time inputs for this date
            const dateInputs = document.querySelectorAll(`td[data-date="${date}"] .time-input`);
            
            // Sum up all minutes
            let totalMinutes = 0;
            dateInputs.forEach(input => {
                const minutes = parseInt(input.dataset.minutes || 0);
                totalMinutes += minutes;
            });
            
            // Update the daily total cell
            cell.textContent = formatMinutes(totalMinutes);
        });
        
        // Optionally recalculate the grand total as well
        const grandTotal = document.getElementById('grand-total');
        if (grandTotal) {
            let totalMinutes = 0;
            
            // Get all time inputs
            const allInputs = document.querySelectorAll('.time-input');
            allInputs.forEach(input => {
                const minutes = parseInt(input.dataset.minutes || 0);
                totalMinutes += minutes;
            });
            
            grandTotal.textContent = formatMinutes(totalMinutes);
            
            // Also update the monthly total if it exists
            const monthlyTotal = document.getElementById('monthly-total');
            if (monthlyTotal) {
                monthlyTotal.textContent = formatMinutes(totalMinutes);
            }
        }
    }
    
    // Quick Log Modal
    const projectSelect = document.getElementById('projectSelect');
    const taskSelect = document.getElementById('taskSelect');
    const quickLogSubmit = document.getElementById('quickLogSubmit');
    
    if (projectSelect) {
        projectSelect.addEventListener('change', function() {
            const projectId = this.value;
            
            if (projectId) {
                // Reset and enable task select
                taskSelect.innerHTML = '<option value="">Loading tasks...</option>';
                taskSelect.disabled = true;
                
                // Fetch tasks for the selected project
                fetch(`/api/projects/${projectId}/tasks`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        taskSelect.innerHTML = '<option value="">Select Task</option>';
                        
                        data.forEach(task => {
                            const option = document.createElement('option');
                            option.value = task.id;
                            option.textContent = `${task.task_number}: ${task.title}`;
                            taskSelect.appendChild(option);
                        });
                        
                        taskSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        taskSelect.innerHTML = '<option value="">Error loading tasks</option>';
                    });
            } else {
                // Reset and disable task select
                taskSelect.innerHTML = '<option value="">Select Task</option>';
                taskSelect.disabled = true;
            }
        });
    }
    
    if (quickLogSubmit) {
        quickLogSubmit.addEventListener('click', function() {
            const taskId = taskSelect.value;
            const workDate = document.getElementById('workDate').value;
            const hours = parseInt(document.getElementById('hours').value) || 0;
            const mins = parseInt(document.getElementById('minutes').value) || 0;
            const description = document.getElementById('description').value;
            
            // Validate input
            if (!taskId) {
                alert('Please select a task');
                return;
            }
            
            if (!workDate) {
                alert('Please select a date');
                return;
            }
            
            if (hours === 0 && mins === 0) {
                alert('Please enter time spent');
                return;
            }
            
            const minutes = (hours * 60) + mins;
            
            // Create form data for submission
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('minutes', minutes);
            formData.append('work_date', workDate);
            formData.append('description', description);
            
            // Submit time log
            fetch(`/projects/${projectSelect.value}/tasks/${taskId}/time-logs`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    // Close modal and reload page
                    const modal = bootstrap.Modal.getInstance(document.getElementById('quickLogModal'));
                    modal.hide();
                    
                    // Reload the page to show the new task
                    window.location.reload();
                } else {
                    throw new Error('Failed to log time');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to log time. Please try again.');
            });
        });
    }
    
    // Fix the z-index issue for overlapping
    const fixZIndex = () => {
        // Keep task column visible when scrolling horizontally
        const taskInfoCells = document.querySelectorAll('.task-info');
        const taskTotalCells = document.querySelectorAll('.task-total');
        
        // Function to handle horizontal scroll
        const handleScroll = () => {
            const scrollLeft = document.querySelector('.timesheet-container').scrollLeft;
            
            // Add appropriate shadow when scrolling to show depth
            if (scrollLeft > 0) {
                document.querySelectorAll('.task-column, .task-info').forEach(el => {
                    el.style.boxShadow = '5px 0 5px -5px rgba(0,0,0,0.2)';
                });
                
                document.querySelectorAll('.total-column, .task-total').forEach(el => {
                    el.style.boxShadow = '-5px 0 5px -5px rgba(0,0,0,0.2)';
                });
            } else {
                document.querySelectorAll('.task-column, .task-info').forEach(el => {
                    el.style.boxShadow = 'none';
                });
            }
        };
        
        // Add scroll event listener
        const timesheetContainer = document.querySelector('.timesheet-container');
        if (timesheetContainer) {
            timesheetContainer.addEventListener('scroll', handleScroll);
        }
    };
    
    // Call the function to fix z-index issues
    fixZIndex();
    
    // Initial calculation of daily totals when the page loads
    recalculateDailyTotals();
});
</script>
@endpush