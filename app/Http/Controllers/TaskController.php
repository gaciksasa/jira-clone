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
        $query = $project->tasks()->with(['status', 'type', 'priority', 'assignee', 'reporter', 'sprint']);
        
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
        
        // Explicitly get statuses for this project
        $statuses = $project->taskStatuses()->orderBy('order')->get();
        
        // Verify we have statuses
        if ($statuses->isEmpty()) {
            // Log this unexpected situation for debugging
            \Log::warning("Project {$project->id} has no task statuses!");
            
            // Create default statuses if none exist
            $defaultStatuses = [
                ['name' => 'To Do', 'slug' => 'to-do', 'order' => 1],
                ['name' => 'In Progress', 'slug' => 'in-progress', 'order' => 2],
                ['name' => 'In Review', 'slug' => 'in-review', 'order' => 3],
                ['name' => 'Done', 'slug' => 'done', 'order' => 4],
            ];

            foreach ($defaultStatuses as $status) {
                TaskStatus::create([
                    'name' => $status['name'],
                    'slug' => $status['slug'],
                    'order' => $status['order'],
                    'project_id' => $project->id,
                ]);
            }
            
            // Reload statuses after creating them
            $statuses = $project->taskStatuses()->orderBy('order')->get();
        }
        
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
            'labels'
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
        ]);

        // Generate a task number
        $latestTask = $project->tasks()->latest('id')->first();
        $taskCount = $latestTask ? intval(explode('-', $latestTask->task_number)[1]) + 1 : 1;
        $taskNumber = $project->key . '-' . $taskCount;
        
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
        ]);
        
        // Attach labels
        if ($request->has('labels')) {
            $task->labels()->attach($request->labels);
        }
        
        // Log activity
        $this->logUserActivity('Created task: ' . $task->task_number . ' - ' . $task->title);
        
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
            'comments.user'
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
        // Check if the task belongs to the project and user is a member
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
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
        ]);

        $changes = [];
        $originalAssignee = $task->assignee_id;
        $originalStatus = $task->task_status_id;
        
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'assignee_id' => $request->assignee_id,
            'task_status_id' => $request->task_status_id,
            'task_type_id' => $request->task_type_id,
            'priority_id' => $request->priority_id,
            'sprint_id' => $request->sprint_id,
            'story_points' => $request->story_points,
        ]);

        // Track specific changes for more detailed logging
        if ($originalAssignee != $request->assignee_id) {
            $assigneeName = $request->assignee_id ? User::find($request->assignee_id)->name : 'Unassigned';
            $changes[] = 'changed assignee to ' . $assigneeName;
        }
        
        if ($originalStatus != $request->task_status_id) {
            $statusName = TaskStatus::find($request->task_status_id)->name;
            $changes[] = 'changed status to ' . $statusName;
        }
        
        // Sync labels
        $task->labels()->sync($request->labels ?? []);
        
        // Log activity with change details if available
        $activityDescription = 'Updated task: ' . $task->task_number;
        if (!empty($changes)) {
            $activityDescription .= ' (' . implode(', ', $changes) . ')';
        }
        
        $this->logUserActivity($activityDescription);
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Task updated successfully.');
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
     * Update task status (for drag and drop functionality).
     */
    public function updateStatus(Request $request, Project $project, Task $task)
    {
        // Check if the task belongs to the project and user is a member
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        // NEW PERMISSION CHECK: Only allow admin, project_manager, or assigned user to move the task
        $user = auth()->user();
        if (!($user->hasRole('admin') || $user->hasRole('project_manager') || $task->assignee_id === $user->id)) {
            return response()->json(['error' => 'You can only move tasks assigned to you.'], 403);
        }
        
        $request->validate([
            'task_status_id' => 'required|exists:task_statuses,id',
        ]);
        
        $task->update([
            'task_status_id' => $request->task_status_id,
        ]);
        
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
        
        $task->comments()->create([
            'content' => $request->content,
            'user_id' => Auth::id(),
        ]);
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Comment added successfully.');
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
        
        // Find the "Closed" status or create it if it doesn't exist
        $closedStatus = $project->taskStatuses()
            ->where('slug', 'closed')
            ->first();
        
        if (!$closedStatus) {
            // Create a "Closed" status with the highest order
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
}