<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskType;
use App\Models\User;
use App\Traits\LogsUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    use LogsUserActivity;

    /**
     * Display a listing of the tasks for a project.
     */
    public function index(Request $request, Project $project)
    {
        // Check if the user is a member of the project
        $this->authorize('view', $project);

        // Build the query
        $query = $project->tasks()->with([
            'status', 
            'type', 
            'priority', 
            'assignee', 
            'reporter', 
            'sprint',
            'subtasks'
        ]);
        
        // Apply filters
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('task_number', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status') && !empty($request->status)) {
            $query->where('task_status_id', $request->status);
        }
        
        if ($request->has('type') && !empty($request->type)) {
            $query->where('task_type_id', $request->type);
        }
        
        if ($request->has('assignee')) {
            if ($request->assignee === 'unassigned') {
                $query->whereNull('assignee_id');
            } elseif (!empty($request->assignee)) {
                $query->where('assignee_id', $request->assignee);
            }
        }
        
        // Add subtask filter
        if ($request->has('subtask_status') && !empty($request->subtask_status)) {
            $subtaskStatus = $request->subtask_status;
            
            if ($subtaskStatus === 'incomplete') {
                // Tasks with incomplete subtasks
                $query->whereHas('subtasks', function($q) {
                    $q->where('is_completed', false);
                });
            } elseif ($subtaskStatus === 'complete') {
                // Tasks where all subtasks are complete
                $query->whereDoesntHave('subtasks', function($q) {
                    $q->where('is_completed', false);
                })->whereHas('subtasks');
            } elseif ($subtaskStatus === 'no_subtasks') {
                // Tasks with no subtasks
                $query->doesntHave('subtasks');
            }
        }
        
        $tasks = $query->get();

        return view('projects.tasks.index', compact('project', 'tasks'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(Project $project)
    {
        // Check if the user is a member of the project
        $this->authorize('view', $project);
        
        // Get parent task info if creating a subtask
        $parentTask = null;
        if (request()->has('parent_id')) {
            $parentTask = Task::find(request()->parent_id);
            if ($parentTask && $parentTask->project_id != $project->id) {
                $parentTask = null; // Reset if parent task is from different project
            }
        }
        
        // Get statuses, types, etc.
        $statuses = $project->taskStatuses()->orderBy('order')->get();
        $types = TaskType::all();
        $priorities = Priority::orderBy('order')->get();
        $sprints = $project->sprints;
        $users = $project->members;
        $labels = $project->labels;
        
        return view('projects.tasks.create', compact(
            'project', 
            'statuses', 
            'types', 
            'priorities', 
            'sprints', 
            'users', 
            'labels',
            'parentTask'
        ));
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Check if the user is a member of the project
        $this->authorize('view', $project);
        
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'task_status_id' => 'required|exists:task_statuses,id',
            'task_type_id' => 'required|exists:task_types,id',
            'priority_id' => 'required|exists:priorities,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'assignee_id' => 'nullable|exists:users,id',
            'story_points' => 'nullable|integer|min:1|max:100',
            'labels' => 'array|nullable',
            'labels.*' => 'exists:labels,id',
            'parent_id' => 'nullable|exists:tasks,id',
        ]);

        // Check if parent_id is valid (task exists and belongs to this project)
        if ($request->parent_id) {
            $parentTask = Task::find($request->parent_id);
            if (!$parentTask || $parentTask->project_id !== $project->id) {
                return redirect()->back()->with('error', 'Invalid parent task selected.');
            }
        }

        // Generate a task number
        $latestTask = $project->tasks()->latest('id')->first();
        $taskCount = $latestTask ? intval(explode('-', $latestTask->task_number)[1]) + 1 : 1;
        $taskNumber = $project->key . '-' . $taskCount;
        
        // Calculate order if this is a subtask
        $order = null;
        if ($request->parent_id) {
            $maxOrder = Task::where('parent_id', $request->parent_id)->max('order') ?? 0;
            $order = $maxOrder + 1;
        }
        
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'task_number' => $taskNumber,
            'project_id' => $project->id,
            'reporter_id' => Auth::id(),
            'assignee_id' => $request->assignee_id,
            'task_status_id' => $request->task_status_id,
            'task_type_id' => $request->task_type_id,
            'priority_id' => $request->priority_id,
            'sprint_id' => $request->sprint_id,
            'story_points' => $request->story_points,
            'parent_id' => $request->parent_id,
            'order' => $order,
        ]);
        
        // Notify assignee if task is assigned to someone else
        if ($task->assignee_id && $task->assignee_id != Auth::id()) {
            try {
                $task->assignee->notify(new \App\Notifications\TaskAssigned($task, Auth::user()));
            } catch (\Exception $e) {
                // Log the error but don't stop execution
                \Log::error('Failed to send notification: ' . $e->getMessage());
            }
        }
        
        // Attach labels
        if ($request->has('labels')) {
            $task->labels()->attach($request->labels);
        }
        
        // Log activity
        $this->logUserActivity('Created task: ' . $task->task_number . ' - ' . $task->title);
        
        // If created as a subtask, redirect to parent
        if ($request->parent_id) {
            $parentTask = Task::find($request->parent_id);
            return redirect()->route('projects.tasks.show', [$project, $parentTask])
                ->with('success', 'Subtask created successfully.');
        }
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(Project $project, Task $task)
    {
        // Check if the task belongs to the project and user is a member
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        $task->load([
            'status', 
            'type', 
            'priority', 
            'assignee', 
            'reporter', 
            'sprint', 
            'labels',
            'comments.user',
            'timeLogs.user',
            'subtasks.assignee'
        ]);
        
        return view('projects.tasks.show', compact('project', 'task'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Project $project, Task $task)
    {
        // Check if the task belongs to the project and user is a member
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        $statuses = $project->taskStatuses;
        $types = TaskType::all();
        $priorities = Priority::orderBy('order')->get();
        $sprints = $project->sprints;
        $users = $project->members;
        $labels = $project->labels;
        $selectedLabels = $task->labels->pluck('id')->toArray();
        
        return view('projects.tasks.edit', compact(
            'project', 
            'task', 
            'statuses', 
            'types', 
            'priorities', 
            'sprints', 
            'users', 
            'labels',
            'selectedLabels'
        ));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Project $project, Task $task)
    {
        // Check if the task belongs to the project
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        // Special case for subtask assignment from modal
        if ($request->has('subtask_assignment')) {
            return $this->handleSubtaskAssignment($request, $project, $task);
        }
        
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'task_status_id' => 'required|exists:task_statuses,id',
            'task_type_id' => 'required|exists:task_types,id',
            'priority_id' => 'required|exists:priorities,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'assignee_id' => 'nullable|exists:users,id',
            'story_points' => 'nullable|integer|min:1|max:100',
            'labels' => 'array|nullable',
            'labels.*' => 'exists:labels,id',
            'parent_id' => 'nullable|exists:tasks,id',
        ]);

        // Prevent circular references
        if ($request->parent_id && $request->parent_id == $task->id) {
            return redirect()->back()->with('error', 'A task cannot be a subtask of itself.');
        }
        
        // Check for deeper circular references
        if ($request->parent_id) {
            $parentTask = Task::find($request->parent_id);
            if ($parentTask && $parentTask->parent_id == $task->id) {
                return redirect()->back()->with('error', 'Cannot create a circular reference in subtasks.');
            }
        }
        
        $changes = [];
        $originalAssignee = $task->assignee_id;
        $originalStatus = $task->task_status_id;
        $originalParent = $task->parent_id;
        
        // Calculate order if parent changed
        $newOrder = $task->order;
        if ($request->parent_id != $originalParent) {
            if ($request->parent_id) {
                $maxOrder = Task::where('parent_id', $request->parent_id)->max('order') ?? 0;
                $newOrder = $maxOrder + 1;
            } else {
                $newOrder = null; // No parent, no order needed
            }
        }

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'assignee_id' => $request->assignee_id,
            'task_status_id' => $request->task_status_id,
            'task_type_id' => $request->task_type_id,
            'priority_id' => $request->priority_id,
            'sprint_id' => $request->sprint_id,
            'story_points' => $request->story_points,
            'parent_id' => $request->parent_id,
            'order' => $newOrder,
        ]);

        // Send notification if assignee has changed and is not the current user
        if ($originalAssignee != $request->assignee_id && $request->assignee_id && $request->assignee_id != Auth::id()) {
            try {
                $task->assignee->notify(new \App\Notifications\TaskAssigned($task, Auth::user()));
            } catch (\Exception $e) {
                // Log the error but don't stop execution
                \Log::error('Failed to send notification: ' . $e->getMessage());
            }
        }
        
        // Send notification if a user was unassigned
        if ($originalAssignee && $originalAssignee != $request->assignee_id) {
            // Only notify if there was an assignee before and it's different from current user
            if ($originalAssignee != Auth::id()) {
                try {
                    $previousAssignee = User::find($originalAssignee);
                    if ($previousAssignee) {
                        $previousAssignee->notify(new \App\Notifications\TaskUnassigned($task, Auth::user()));
                    }
                } catch (\Exception $e) {
                    // Log the error but don't stop execution
                    \Log::error('Failed to send unassignment notification: ' . $e->getMessage());
                }
            }
        }

        // Track changes for logging
        if ($originalAssignee != $request->assignee_id) {
            $assigneeName = $request->assignee_id ? User::find($request->assignee_id)->name : 'Unassigned';
            $changes[] = 'changed assignee to ' . $assigneeName;
        }
        
        if ($originalStatus != $request->task_status_id) {
            $statusName = TaskStatus::find($request->task_status_id)->name;
            $changes[] = 'changed status to ' . $statusName;
        }
        
        if ($originalParent != $request->parent_id) {
            if ($request->parent_id) {
                $parentTask = Task::find($request->parent_id);
                $changes[] = 'assigned as subtask of ' . $parentTask->task_number;
            } else if ($originalParent) {
                $changes[] = 'removed from parent task';
            }
        }
        
        // Sync labels
        $task->labels()->sync($request->labels ?? []);
        
        // Log activity with change details
        $activityDescription = 'Updated task: ' . $task->task_number;
        if (!empty($changes)) {
            $activityDescription .= ' (' . implode(', ', $changes) . ')';
        }
        
        $this->logUserActivity($activityDescription);
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Handle subtask assignment from the modal form
     */
    private function handleSubtaskAssignment(Request $request, Project $project, Task $parentTask)
    {
        $request->validate([
            'subtask_id' => 'required|exists:tasks,id',
        ]);
        
        $subtaskId = $request->subtask_id;
        $subtask = Task::findOrFail($subtaskId);
        
        // Check if subtask belongs to the same project
        if ($subtask->project_id !== $project->id) {
            return redirect()->back()->with('error', 'The selected task does not belong to this project.');
        }
        
        // Check if trying to assign a task to itself
        if ($subtask->id === $parentTask->id) {
            return redirect()->back()->with('error', 'A task cannot be a subtask of itself.');
        }
        
        // Check if already a subtask elsewhere
        if ($subtask->parent_id) {
            return redirect()->back()->with('error', 'The selected task is already a subtask of another task.');
        }
        
        // Check for circular references
        if ($parentTask->parent_id === $subtask->id) {
            return redirect()->back()->with('error', 'Cannot create a circular reference.');
        }
        
        // Calculate order (max order + 1)
        $maxOrder = $parentTask->subtasks()->max('order') ?? 0;
        
        // Update the task
        $subtask->update([
            'parent_id' => $parentTask->id,
            'order' => $maxOrder + 1,
        ]);
        
        // Log activity
        $this->logUserActivity('Assigned task ' . $subtask->task_number . ' as subtask of: ' . $parentTask->task_number);
        
        return redirect()->route('projects.tasks.show', [$project, $parentTask])
            ->with('success', 'Task successfully assigned as subtask.');
    }


    /**
     * Remove the specified task from storage.
     */
    public function destroy(Project $project, Task $task)
    {
        // Check if the task belongs to the project and user has permission
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('delete', $project);
        
        // Log activity before deletion
        $this->logUserActivity('Deleted task: ' . $task->task_number . ' - ' . $task->title);
        
        $task->delete();
        
        return redirect()->route('projects.tasks.index', $project)
            ->with('success', 'Task deleted successfully.');
    }

    /**
     * Show the form for editing a subtask.
     */
    public function editSubtask(Project $project, Task $task, Task $subtask)
    {
        // Check if the tasks belong to the project and if subtask belongs to task
        if ($task->project_id !== $project->id || $subtask->parent_id !== $task->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        $statuses = $project->taskStatuses()->orderBy('order')->get();
        $types = TaskType::all();
        $priorities = Priority::orderBy('order')->get();
        $users = $project->members;
        
        return view('projects.tasks.edit_subtask', compact(
            'project',
            'task',
            'subtask',
            'statuses',
            'types',
            'priorities',
            'users'
        ));
    }

    /**
     * Update a subtask.
     */
    public function updateSubtask(Request $request, Project $project, Task $task, Task $subtask)
    {
        // Check if the tasks belong to the project and if subtask belongs to task
        if ($task->project_id !== $project->id || $subtask->parent_id !== $task->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'task_status_id' => 'required|exists:task_statuses,id',
            'task_type_id' => 'required|exists:task_types,id',
            'priority_id' => 'required|exists:priorities,id',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $originalAssignee = $subtask->assignee_id;
        
        $subtask->update([
            'title' => $request->title,
            'description' => $request->description,
            'assignee_id' => $request->assignee_id,
            'task_status_id' => $request->task_status_id,
            'task_type_id' => $request->task_type_id,
            'priority_id' => $request->priority_id,
        ]);

        // Send notification if assignee has changed and is not the current user
        if ($originalAssignee != $request->assignee_id && $request->assignee_id && $request->assignee_id != Auth::id()) {
            try {
                $subtask->assignee->notify(new \App\Notifications\TaskAssigned($subtask, Auth::user()));
            } catch (\Exception $e) {
                // Log the error but don't stop execution
                \Log::error('Failed to send notification: ' . $e->getMessage());
            }
        }
        
        // Send notification if a user was unassigned
        if ($originalAssignee && $originalAssignee != $request->assignee_id) {
            // Only notify if there was an assignee before and it's different from current user
            if ($originalAssignee != Auth::id()) {
                try {
                    $previousAssignee = User::find($originalAssignee);
                    if ($previousAssignee) {
                        $previousAssignee->notify(new \App\Notifications\TaskUnassigned($subtask, Auth::user()));
                    }
                } catch (\Exception $e) {
                    // Log the error but don't stop execution
                    \Log::error('Failed to send unassignment notification: ' . $e->getMessage());
                }
            }
        }
        
        // Log activity
        $this->logUserActivity('Updated subtask ' . $subtask->task_number . ' for task: ' . $task->task_number);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'subtask' => $subtask->load('assignee', 'status', 'type', 'priority'),
                'message' => 'Subtask updated successfully'
            ]);
        }
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Subtask updated successfully.');
    }

    /**
     * Remove a subtask.
     */
    public function destroySubtask(Project $project, Task $task, Task $subtask)
    {
        // Check if the tasks belong to the project and if subtask belongs to task
        if ($task->project_id !== $project->id || $subtask->parent_id !== $task->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        // Log activity before deletion
        $this->logUserActivity('Deleted subtask ' . $subtask->task_number . ' from task: ' . $task->task_number);
        
        $subtask->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subtask deleted successfully'
            ]);
        }
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Subtask deleted successfully.');
    }

    /**
     * Update task status (for drag and drop functionality).
     */
    public function updateStatus(Request $request, Project $project, Task $task)
    {
        // Check if the task belongs to the project
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        // Check if the user can move this specific task
        $user = Auth::user();
        if (!$user->canMoveTask($task)) {
            return response()->json(['error' => 'You can only move tasks assigned to you.'], 403);
        }
        
        $request->validate([
            'task_status_id' => 'required|exists:task_statuses,id',
        ]);
        
        // Check if the target status belongs to this project
        $targetStatus = TaskStatus::find($request->task_status_id);
        if (!$targetStatus || $targetStatus->project_id !== $project->id) {
            return response()->json(['error' => 'Invalid target status.'], 400);
        }
        
        // Get old status name for logging
        $oldStatusName = $task->status->name;

        $originalAssignee = $task->assignee_id;
        
        $task->update([
            'task_status_id' => $request->task_status_id,
            'assignee_id' => $request->assignee_id ?? $task->assignee_id,
        ]);

        // Send notification if assignee has changed and is not the current user
        if (isset($request->assignee_id) && $originalAssignee != $request->assignee_id && $request->assignee_id && $request->assignee_id != Auth::id()) {
            try {
                $task->assignee->notify(new \App\Notifications\TaskAssigned($task, Auth::user()));
            } catch (\Exception $e) {
                // Log the error but don't stop execution
                \Log::error('Failed to send notification: ' . $e->getMessage());
            }
        }
        
        // Send notification if a user was unassigned (board task drag operation)
        if (isset($request->assignee_id) && $originalAssignee && $originalAssignee != $request->assignee_id) {
            // Only notify if there was an assignee before and it's different from current user
            if ($originalAssignee != Auth::id()) {
                try {
                    $previousAssignee = User::find($originalAssignee);
                    if ($previousAssignee) {
                        $previousAssignee->notify(new \App\Notifications\TaskUnassigned($task, Auth::user()));
                    }
                } catch (\Exception $e) {
                    // Log the error but don't stop execution
                    \Log::error('Failed to send unassignment notification: ' . $e->getMessage());
                }
            }
        }
        
        // Log activity
        $this->logUserActivity('Moved task ' . $task->task_number . ' from ' . $oldStatusName . ' to ' . $targetStatus->name);
        
        return response()->json(['success' => true]);
    }

    /**
     * Add a comment to a task.
     */
    public function addComment(Request $request, Project $project, Task $task)
    {
        // Check if the task belongs to the project and user is a member
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        $request->validate([
            'content' => 'required',
        ]);
        
        // Directly store the content from the editor
        $task->comments()->create([
            'content' => $request->content,
            'user_id' => auth()->id(),
        ]);
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Display a listing of tasks with a specific label.
     */
    public function indexByLabel(Project $project, Label $label)
    {
        // Check if the label belongs to the project
        if ($label->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user is a member of the project
        $this->authorize('view', $project);
        
        // Get tasks with this label
        $tasks = $label->tasks()
            ->where('project_id', $project->id)
            ->with(['status', 'type', 'priority', 'assignee', 'reporter', 'sprint', 'subtasks'])
            ->get();
        
        return view('projects.tasks.by-label', compact('project', 'label', 'tasks'));
    }

    /**
     * Detach a task from its parent (remove the subtask relationship)
     */
    public function detachFromParent(Project $project, Task $task)
    {
        // Check if the task belongs to the project
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        // Only proceed if the task is actually a subtask
        if ($task->parent_id) {
            $parentNumber = $task->parent->task_number;
            
            $task->update([
                'parent_id' => null,
                'order' => null,
            ]);
            
            // Log activity
            $this->logUserActivity('Removed task ' . $task->task_number . ' as subtask of ' . $parentNumber);
            
            return redirect()->route('projects.tasks.show', [$project, $task])
                ->with('success', 'Task is no longer a subtask.');
        }
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('info', 'Task is not a subtask.');
    }

    /**
     * Close the specified task.
     */
    public function close(Project $project, Task $task)
    {
        // Check if the task belongs to the project and user is a member
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        // Find the "Closed" status or a status with slug "closed"
        $closedStatus = $project->taskStatuses()
            ->where('slug', 'closed')
            ->orWhere('name', 'Closed')
            ->first();
        
        if (!$closedStatus) {
            // Create a "Closed" status with the highest order only if it doesn't exist
            $maxOrder = $project->taskStatuses()->max('order') ?? 0;
            $closedStatus = TaskStatus::create([
                'name' => 'Closed',
                'slug' => 'closed',
                'order' => $maxOrder + 1,
                'project_id' => $project->id,
                'color' => '#999999', // Default grey color
            ]);
        }
        
        // Update the task status to "Closed"
        $task->update([
            'task_status_id' => $closedStatus->id,
            'closed_at' => now(),
        ]);
        
        // Log activity
        $this->logUserActivity('Closed task: ' . $task->task_number . ' - ' . $task->title);
        
        return redirect()->back()
            ->with('success', 'Task closed successfully.');
    }

    /**
     * Reopen the specified task.
     */
    public function reopen(Project $project, Task $task)
    {
        // Check if the task belongs to the project and user is a member
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        // Find the "To Do" status (or the first status)
        $toDoStatus = $project->taskStatuses()
            ->where('slug', 'to-do')
            ->first();
        
        if (!$toDoStatus) {
            // Fallback to the first status if "To Do" doesn't exist
            $toDoStatus = $project->taskStatuses()
                ->orderBy('order')
                ->first();
        }
        
        // Update the task status to "To Do" (or the first status)
        $task->update([
            'task_status_id' => $toDoStatus->id,
            'closed_at' => null,
        ]);
        
        // Log activity
        $this->logUserActivity('Reopened task: ' . $task->task_number . ' - ' . $task->title);
        
        return redirect()->back()
            ->with('success', 'Task reopened successfully.');
    }

    /**
     * Create a subtask for the specified task.
     */
    public function createSubtask(Project $project, Task $task)
    {
        // Check if the task belongs to the project
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        $statuses = $project->taskStatuses()->orderBy('order')->get();
        $types = TaskType::all();
        $priorities = Priority::orderBy('order')->get();
        $users = $project->members;
        
        return view('projects.tasks.create_subtask', compact(
            'project', 
            'task',
            'statuses', 
            'types', 
            'priorities', 
            'users'
        ));
    }

    /**
     * Store a newly created subtask for the specified task.
     */
    public function storeSubtask(Request $request, Project $project, Task $task)
    {
        // Check if the task belongs to the project
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'task_status_id' => 'required|exists:task_statuses,id',
            'task_type_id' => 'required|exists:task_types,id',
            'priority_id' => 'required|exists:priorities,id',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        // Generate a task number for subtask
        $latestTask = $project->tasks()->latest('id')->first();
        $taskCount = $latestTask ? intval(explode('-', $latestTask->task_number)[1]) + 1 : 1;
        $taskNumber = $project->key . '-' . $taskCount;
        
        // Calculate the order (should be max order + 1)
        $maxOrder = $task->subtasks()->max('order') ?? 0;
        
        $subtask = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'task_number' => $taskNumber,
            'project_id' => $project->id,
            'parent_id' => $task->id,
            'reporter_id' => Auth::id(),
            'assignee_id' => $request->assignee_id,
            'task_status_id' => $request->task_status_id,
            'task_type_id' => $request->task_type_id,
            'priority_id' => $request->priority_id,
            'order' => $maxOrder + 1,
        ]);

        // Notify assignee if subtask is assigned to someone else
        if ($subtask->assignee_id && $subtask->assignee_id != Auth::id()) {
            try {
                $subtask->assignee->notify(new \App\Notifications\TaskAssigned($subtask, Auth::user()));
            } catch (\Exception $e) {
                // Log the error but don't stop execution
                \Log::error('Failed to send notification: ' . $e->getMessage());
            }
        }
        
        // Log activity
        $this->logUserActivity('Created subtask ' . $subtask->task_number . ' for task: ' . $task->task_number);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'subtask' => $subtask->load('assignee', 'status', 'type', 'priority'),
                'message' => 'Subtask created successfully'
            ]);
        }
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Subtask created successfully.');
    }

    /**
     * Reorder subtasks.
     */
    public function reorderSubtasks(Request $request, Project $project, Task $task)
    {
        // Check if the task belongs to the project
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        $request->validate([
            'subtasks' => 'required|array',
            'subtasks.*' => 'exists:tasks,id',
        ]);
        
        // Update the order of subtasks
        foreach ($request->subtasks as $index => $subtaskId) {
            // Verify that the subtask belongs to this task
            $subtask = Task::find($subtaskId);
            if ($subtask && $subtask->parent_id === $task->id) {
                $subtask->update(['order' => $index + 1]);
            }
        }
        
        // Log activity
        $this->logUserActivity('Reordered subtasks for task: ' . $task->task_number);
        
        return response()->json(['success' => true]);
    }
}