<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SprintController extends Controller
{
    /**
     * Display a listing of the sprints.
     */
    public function index(Project $project)
    {
        // Check if the user is a member of the project
        $this->authorize('view', $project);
        
        $sprints = $project->sprints()
            ->withCount('tasks')
            ->orderBy('status')
            ->orderByDesc('end_date')
            ->get();
        
        return view('projects.sprints.index', compact('project', 'sprints'));
    }

    /**
     * Show the form for creating a new sprint.
     */
    public function create(Project $project)
    {
        // Check if the user is a member of the project
        $this->authorize('view', $project);
        
        return view('projects.sprints.create', compact('project'));
    }

    /**
     * Store a newly created sprint in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Check if the user is a member of the project
        $this->authorize('view', $project);
        
        $request->validate([
            'name' => 'required|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:planning,active,completed',
        ]);
        
        $sprint = Sprint::create([
            'name' => $request->name,
            'project_id' => $project->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
        ]);
        
        return redirect()->route('projects.sprints.show', [$project, $sprint])
            ->with('success', 'Sprint created successfully.');
    }

    /**
     * Display the specified sprint.
     */
    public function show(Project $project, Sprint $sprint)
    {
        // Check if the sprint belongs to the project and user is a member
        if ($sprint->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        $tasks = $sprint->tasks()
            ->with(['status', 'type', 'priority', 'assignee'])
            ->get()
            ->groupBy('task_status_id');
        
        $statuses = $project->taskStatuses;
        
        return view('projects.sprints.show', compact('project', 'sprint', 'tasks', 'statuses'));
    }

    /**
     * Show the form for editing the specified sprint.
     */
    public function edit(Project $project, Sprint $sprint)
    {
        // Check if the sprint belongs to the project and user is a member
        if ($sprint->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        return view('projects.sprints.edit', compact('project', 'sprint'));
    }

    /**
     * Update the specified sprint in storage.
     */
    public function update(Request $request, Project $project, Sprint $sprint)
    {
        // Check if the sprint belongs to the project and user is a member
        if ($sprint->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        $request->validate([
            'name' => 'required|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:planning,active,completed',
        ]);
        
        $sprint->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
        ]);
        
        return redirect()->route('projects.sprints.show', [$project, $sprint])
            ->with('success', 'Sprint updated successfully.');
    }

    /**
     * Remove the specified sprint from storage.
     */
    public function destroy(Project $project, Sprint $sprint)
    {
        // Check if the sprint belongs to the project and user has permission
        if ($sprint->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('delete', $project);
        
        // Remove sprint association from tasks
        $sprint->tasks()->update(['sprint_id' => null]);
        
        // Delete the sprint
        $sprint->delete();
        
        return redirect()->route('projects.sprints.index', $project)
            ->with('success', 'Sprint deleted successfully.');
    }

    /**
     * Start a sprint.
     */
    public function start(Project $project, Sprint $sprint)
    {
        // Check if the sprint belongs to the project and user is a member
        if ($sprint->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        // Update sprint status to active
        $sprint->update([
            'status' => 'active',
            'start_date' => now(),
        ]);
        
        return redirect()->route('projects.sprints.show', [$project, $sprint])
            ->with('success', 'Sprint started successfully.');
    }

    /**
     * Complete a sprint.
     */
    public function complete(Project $project, Sprint $sprint)
    {
        // Check if the sprint belongs to the project and user is a member
        if ($sprint->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        // Update sprint status to completed
        $sprint->update([
            'status' => 'completed',
            'end_date' => now(),
        ]);
        
        return redirect()->route('projects.sprints.show', [$project, $sprint])
            ->with('success', 'Sprint completed successfully.');
    }

    /**
     * Manage sprint backlog.
     */
    public function backlog(Project $project, Sprint $sprint)
    {
        // Check if the sprint belongs to the project and user is a member
        if ($sprint->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        // Get tasks in the sprint
        $sprintTasks = $sprint->tasks()
            ->with(['status', 'type', 'priority', 'assignee'])
            ->get();
        
        // Get project tasks not in any sprint
        $backlogTasks = $project->tasks()
            ->whereNull('sprint_id')
            ->with(['status', 'type', 'priority', 'assignee'])
            ->get();
        
        return view('projects.sprints.backlog', compact('project', 'sprint', 'sprintTasks', 'backlogTasks'));
    }

    /**
     * Add tasks to a sprint.
     */
    public function addTasks(Request $request, Project $project, Sprint $sprint)
    {
        // Check if the sprint belongs to the project and user is a member
        if ($sprint->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);
        
        // Update sprint_id for selected tasks
        $project->tasks()
            ->whereIn('id', $request->task_ids)
            ->update(['sprint_id' => $sprint->id]);
        
        return redirect()->route('projects.sprints.backlog', [$project, $sprint])
            ->with('success', 'Tasks added to sprint successfully.');
    }

    /**
     * Remove tasks from a sprint.
     */
    public function removeTasks(Request $request, Project $project, Sprint $sprint)
    {
        // Check if the sprint belongs to the project and user is a member
        if ($sprint->project_id !== $project->id) {
            abort(404);
        }
        
        $this->authorize('view', $project);
        
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);
        
        // Remove tasks from sprint
        $project->tasks()
            ->whereIn('id', $request->task_ids)
            ->where('sprint_id', $sprint->id)
            ->update(['sprint_id' => null]);
        
        return redirect()->route('projects.sprints.backlog', [$project, $sprint])
            ->with('success', 'Tasks removed from sprint successfully.');
    }
}