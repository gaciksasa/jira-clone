<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TaskStatus;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Traits\LogsUserActivity;

class TaskStatusController extends Controller
{
    use LogsUserActivity;

    /**
     * Display a listing of the statuses for a project.
     */
    public function index(Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        $statuses = $project->taskStatuses()->orderBy('order')->get();
        
        return view('projects.statuses.index', compact('project', 'statuses'));
    }

    /**
     * Show the form for creating a new status.
     */
    public function create(Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        return view('projects.statuses.create', compact('project'));
    }

    /**
     * Store a newly created status in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        $request->validate([
            'name' => 'required|max:255',
            'color' => 'nullable|max:50',
        ]);
        
        // Get the highest order value
        $maxOrder = $project->taskStatuses()->max('order') ?? 0;
        
        TaskStatus::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color ?? '#6c757d', // Default grey if no color provided
            'order' => $maxOrder + 1,
            'project_id' => $project->id,
        ]);
        
        // Log activity
        $this->logUserActivity('Created board column: ' . $request->name . ' for project ' . $project->name);
        
        return redirect()->route('projects.statuses.index', $project)
            ->with('success', 'Board column created successfully.');
    }

    /**
     * Show the form for editing the specified status.
     */
    public function edit(Project $project, TaskStatus $taskStatus)
    {
        // Check if the status belongs to the project
        if ($taskStatus->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        return view('projects.statuses.edit', compact('project', 'taskStatus'));
    }

    /**
     * Update the specified status in storage.
     */
    public function update(Request $request, Project $project, TaskStatus $taskStatus)
    {
        // Check if the status belongs to the project
        if ($taskStatus->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        $request->validate([
            'name' => 'required|max:255',
            'color' => 'nullable|max:50',
        ]);
        
        $oldName = $taskStatus->name;
        
        $taskStatus->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color ?? '#6c757d',
        ]);
        
        // Log activity
        $this->logUserActivity('Updated board column from "' . $oldName . '" to "' . $request->name . '" for project ' . $project->name);
        
        return redirect()->route('projects.statuses.index', $project)
            ->with('success', 'Board column updated successfully.');
    }

    /**
     * Remove the specified status from storage.
     */
    public function destroy(Request $request, Project $project, TaskStatus $taskStatus)
    {
        // Check if the status belongs to the project
        if ($taskStatus->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        // Check if the project has at least one other status
        if ($project->taskStatuses()->count() <= 1) {
            return redirect()->route('projects.statuses.index', $project)
                ->with('error', 'Cannot delete the only board column. Create another column first.');
        }
        
        // Get target status for reassigning tasks
        $request->validate([
            'target_status_id' => 'required|exists:task_statuses,id',
        ]);
        
        $targetStatusId = $request->target_status_id;
        $targetStatus = TaskStatus::find($targetStatusId);
        
        // Check if target status belongs to this project
        if ($targetStatus->project_id !== $project->id) {
            return redirect()->route('projects.statuses.index', $project)
                ->with('error', 'Invalid target board column selected.');
        }
        
        // Update all tasks from the deleted status to the target status
        Task::where('task_status_id', $taskStatus->id)
            ->update(['task_status_id' => $targetStatusId]);
        
        // Log activity
        $this->logUserActivity('Deleted board column "' . $taskStatus->name . '" for project ' . $project->name . ' and moved tasks to "' . $targetStatus->name . '"');
        
        // Delete the status
        $taskStatus->delete();
        
        // Reorder remaining statuses
        $remainingStatuses = $project->taskStatuses()->orderBy('order')->get();
        foreach ($remainingStatuses as $index => $status) {
            $status->update(['order' => $index + 1]);
        }
        
        return redirect()->route('projects.statuses.index', $project)
            ->with('success', 'Board column deleted successfully and tasks reassigned.');
    }

    /**
     * Reorder statuses using AJAX.
     */
    public function reorder(Request $request, Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        $request->validate([
            'statuses' => 'required|array',
            'statuses.*' => 'exists:task_statuses,id',
        ]);
        
        // Update the order of statuses
        foreach ($request->statuses as $index => $statusId) {
            // Verify that the status belongs to this project
            $status = TaskStatus::find($statusId);
            if ($status && $status->project_id === $project->id) {
                $status->update(['order' => $index + 1]);
            }
        }
        
        // Log activity
        $this->logUserActivity('Reordered board columns for project ' . $project->name);
        
        return response()->json(['success' => true]);
    }
}