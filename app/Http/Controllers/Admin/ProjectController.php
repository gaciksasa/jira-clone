<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Department;
use App\Models\User;
use App\Traits\LogsUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    use LogsUserActivity;

    /**
     * Display a listing of all projects.
     */
    public function index(Request $request)
    {
        // Check if user has permission to manage projects
        $this->authorize('manage projects');

        $query = Project::query();
        
        // Search by name or key
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('key', 'like', "%{$search}%");
            });
        }
        
        // Filter by department
        if ($request->has('department') && !empty($request->department)) {
            $query->where('department_id', $request->department);
        }
        
        // Filter by lead
        if ($request->has('lead') && !empty($request->lead)) {
            $query->where('lead_id', $request->lead);
        }

        $projects = $query->withCount('tasks')
                         ->with(['department', 'lead'])
                         ->orderBy('updated_at', 'desc')
                         ->get();
                        
        $departments = Department::all(); // For the filter dropdown
        $leads = User::all(); // For the filter dropdown
        
        return view('admin.projects.index', compact('projects', 'departments', 'leads'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        // Check if user has permission to manage projects
        $this->authorize('manage projects');

        $users = User::all();
        $departments = Department::all();
        
        return view('admin.projects.create', compact('users', 'departments'));
    }

    /**
     * Show the specified project.
     */
    public function show(Project $project)
    {
        // Check if user has permission to manage projects
        $this->authorize('manage projects');

        $project->load(['tasks' => function ($query) {
            $query->with(['status', 'type', 'priority', 'assignee']);
        }, 'taskStatuses' => function ($query) {
            $query->orderBy('order');
        }, 'members', 'lead', 'department']);

        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        // Check if user has permission to manage projects
        $this->authorize('manage projects');

        $users = User::all();
        $members = $project->members;
        $departments = Department::all();

        return view('admin.projects.edit', compact('project', 'users', 'members', 'departments'));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        // Check if user has permission to manage projects
        $this->authorize('manage projects');

        // Same validation and update logic as in the main ProjectController
        // ...

        $this->logUserActivity('Admin updated project: ' . $project->name);
        
        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        // Check if user has permission to manage projects
        $this->authorize('manage projects');

        // Log activity before deletion
        $this->logUserActivity('Admin deleted project: ' . $project->name);
        
        // Delete the project
        $project->delete();
        
        return redirect()->route('admin.projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}