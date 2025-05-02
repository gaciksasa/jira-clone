<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TaskStatusController extends Controller
{
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
        
        return redirect()->route('projects.statuses.index', $project)
            ->with('success', 'Status created successfully.');
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
        
        $taskStatus->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color ?? '#6c757d',
        ]);
        
        return redirect()->route('projects.statuses.index', $project)
            ->with('success', 'Status updated successfully.');
    }

    /**
     * Remove the specified status from storage.
     */
    public function destroy(Project $project, TaskStatus $taskStatus)
    {
        // Check if the status belongs to the project
        if ($taskStatus->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        // Check if there are tasks using this status
        $tasksCount = $project->tasks()->where('task_status_id', $taskStatus->id)->count();
        
        if ($tasksCount > 0) {
            return redirect()->route('projects.statuses.index', $project)
                ->with('error', 'Cannot delete status that is being used by tasks. Please reassign those tasks first.');
        }
        
        $taskStatus->delete();
        
        // Reorder remaining statuses
        $remainingStatuses = $project->taskStatuses()->orderBy('order')->get();
        foreach ($remainingStatuses as $index => $status) {
            $status->update(['order' => $index + 1]);
        }
        
        return redirect()->route('projects.statuses.index', $project)
            ->with('success', 'Status deleted successfully.');
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
            TaskStatus::where('id', $statusId)
                ->where('project_id', $project->id)
                ->update(['order' => $index + 1]);
        }
        
        return response()->json(['success' => true]);
    }
}