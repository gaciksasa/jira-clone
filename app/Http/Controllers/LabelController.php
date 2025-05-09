<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\Project;
use App\Traits\LogsUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabelController extends Controller
{
    use LogsUserActivity;

    /**
     * Display a listing of the labels for a project.
     */
    public function index(Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        $labels = $project->labels()->orderBy('name')->get();
        
        return view('projects.labels.index', compact('project', 'labels'));
    }

    /**
     * Show the form for creating a new label.
     */
    public function create(Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        return view('projects.labels.create', compact('project'));
    }

    /**
     * Store a newly created label in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        $request->validate([
            'name' => 'required|max:255',
            'color' => 'required|max:50',
        ]);
        
        $label = Label::create([
            'name' => $request->name,
            'color' => $request->color,
            'project_id' => $project->id,
        ]);
        
        // Log activity
        $this->logUserActivity('Created label: ' . $request->name . ' for project ' . $project->name);
        
        return redirect()->route('projects.labels.index', $project)
            ->with('success', 'Label created successfully.');
    }

    /**
     * Show the form for editing the specified label.
     */
    public function edit(Project $project, Label $label)
    {
        // Check if the label belongs to the project
        if ($label->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        return view('projects.labels.edit', compact('project', 'label'));
    }

    /**
     * Update the specified label in storage.
     */
    public function update(Request $request, Project $project, Label $label)
    {
        // Check if the label belongs to the project
        if ($label->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        $request->validate([
            'name' => 'required|max:255',
            'color' => 'required|max:50',
        ]);
        
        $oldName = $label->name;
        
        $label->update([
            'name' => $request->name,
            'color' => $request->color,
        ]);
        
        // Log activity
        $this->logUserActivity('Updated label from "' . $oldName . '" to "' . $request->name . '" for project ' . $project->name);
        
        return redirect()->route('projects.labels.index', $project)
            ->with('success', 'Label updated successfully.');
    }

    /**
     * Remove the specified label from storage.
     */
    public function destroy(Project $project, Label $label)
    {
        // Check if the label belongs to the project
        if ($label->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can update this project
        $this->authorize('update', $project);
        
        // Check if the label is in use
        $tasksCount = $label->tasks()->count();
        if ($tasksCount > 0) {
            return redirect()->route('projects.labels.index', $project)
                ->with('error', 'Cannot delete label because it is assigned to ' . $tasksCount . ' task(s).');
        }
        
        // Log activity before deletion
        $this->logUserActivity('Deleted label "' . $label->name . '" from project ' . $project->name);
        
        // Delete the label
        $label->delete();
        
        return redirect()->route('projects.labels.index', $project)
            ->with('success', 'Label deleted successfully.');
    }
}