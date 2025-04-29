<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     */
    public function index()
    {
        $projects = Auth::user()->projects()->withCount('tasks')->get();
        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        $users = User::all();
        return view('projects.create', compact('users'));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'key' => 'required|unique:projects|max:10|alpha_num',
            'description' => 'nullable',
            'lead_id' => 'required|exists:users,id',
        ]);

        $project = Project::create([
            'name' => $request->name,
            'key' => Str::upper($request->key),
            'description' => $request->description,
            'lead_id' => $request->lead_id,
        ]);

        // Add the project lead as a member
        $project->members()->attach($request->lead_id);
        
        // Add current user as a member if they're not the lead
        if (Auth::id() != $request->lead_id) {
            $project->members()->attach(Auth::id());
        }

        // Create default task statuses
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

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        // Check if the user is a member of the project
        $this->authorize('view', $project);

        $project->load(['tasks' => function ($query) {
            $query->with(['status', 'type', 'priority', 'assignee']);
        }, 'taskStatuses' => function ($query) {
            $query->orderBy('order');
        }]);

        $statuses = $project->taskStatuses;
        $tasks = $project->tasks;

        return view('projects.show', compact('project', 'statuses', 'tasks'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        // Check if the user can edit this project
        $this->authorize('update', $project);

        $users = User::all();
        $members = $project->members;

        return view('projects.edit', compact('project', 'users', 'members'));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);

        $request->validate([
            'name' => 'required|max:255',
            'key' => 'required|max:10|alpha_num|unique:projects,key,' . $project->id,
            'description' => 'nullable',
            'lead_id' => 'required|exists:users,id',
            'members' => 'array',
            'members.*' => 'exists:users,id',
        ]);

        $project->update([
            'name' => $request->name,
            'key' => Str::upper($request->key),
            'description' => $request->description,
            'lead_id' => $request->lead_id,
        ]);

        // Always ensure lead is a member
        $members = $request->members ?? [];
        if (!in_array($request->lead_id, $members)) {
            $members[] = $request->lead_id;
        }

        // Always ensure current user is a member if they're updating the project
        if (!in_array(Auth::id(), $members)) {
            $members[] = Auth::id();
        }

        // Sync project members
        $project->members()->sync($members);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        // Check if the user can delete this project
        $this->authorize('delete', $project);

        // Delete the project
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Display the project board (Kanban view).
     */
    public function board(Project $project)
    {
        // Check if the user is a member of the project
        $this->authorize('view', $project);

        $statuses = $project->taskStatuses()->orderBy('order')->get();
        
        // Get tasks grouped by status
        $tasks = $project->tasks()
            ->with(['type', 'priority', 'assignee'])
            ->get()
            ->groupBy('task_status_id');

        return view('projects.board', compact('project', 'statuses', 'tasks'));
    }

    /**
     * Manage project members.
     */
    public function members(Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);

        $users = User::all();
        $members = $project->members;

        return view('projects.members', compact('project', 'users', 'members'));
    }

    /**
     * Update project members.
     */
    public function updateMembers(Request $request, Project $project)
    {
        // Check if the user can update this project
        $this->authorize('update', $project);

        $request->validate([
            'members' => 'array',
            'members.*' => 'exists:users,id',
        ]);

        // Always ensure lead is a member
        $members = $request->members ?? [];
        if (!in_array($project->lead_id, $members)) {
            $members[] = $project->lead_id;
        }

        // Always ensure current user is a member if they're updating the project
        if (!in_array(Auth::id(), $members)) {
            $members[] = Auth::id();
        }

        // Sync project members
        $project->members()->sync($members);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project members updated successfully.');
    }
}