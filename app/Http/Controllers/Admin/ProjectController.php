<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Department;
use App\Models\TimeLog;
use App\Models\User;
use App\Traits\LogsUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        // Handle sorting
        $sortField = $request->get('sort_by', 'updated_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['name', 'key', 'tasks_count', 'members_count', 'updated_at', 'created_at', 'department', 'lead'];
        
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'updated_at'; // Default sort field
        }
        
        // Special handling for department sorting
        if ($sortField === 'department') {
            $query->leftJoin('departments', 'projects.department_id', '=', 'departments.id')
                ->select('projects.*')
                ->orderBy('departments.name', $sortDirection);
        } 
        // Special handling for lead sorting
        elseif ($sortField === 'lead') {
            $query->join('users', 'projects.lead_id', '=', 'users.id')
                ->select('projects.*')
                ->orderBy('users.name', $sortDirection);
        }
        // Default sorting
        else {
            $query->orderBy($sortField, $sortDirection);
        }

        $projects = $query->withCount(['tasks', 'members'])
                        ->with(['department', 'lead'])
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
    public function show(Request $request, Project $project)
    {
        // Check if user has permission to manage projects
        $this->authorize('manage projects');

        // Get start and end dates from request or use current month
        $startDate = $request->get('start_date') 
            ? Carbon::createFromFormat('Y-m-d', $request->get('start_date'))
            : Carbon::now()->startOfMonth();
            
        $endDate = $request->get('end_date') 
            ? Carbon::createFromFormat('Y-m-d', $request->get('end_date'))
            : Carbon::now()->endOfMonth();
            
        // Make sure end date is not before start date
        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy()->addMonth();
        }
        
        // Get project members
        $members = $project->members;
        
        // Get all time logs for this project
        $timeLogs = TimeLog::whereHas('task', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            })
            ->whereBetween('work_date', [$startDate, $endDate])
            ->with(['task', 'user'])
            ->get();
        
        // Group by user
        $userTotals = [];
        foreach ($members as $member) {
            $userLogs = $timeLogs->where('user_id', $member->id);
            $userTotals[$member->id] = [
                'user' => $member,
                'total_minutes' => $userLogs->sum('minutes'),
                'formatted_total' => $this->formatMinutes($userLogs->sum('minutes')),
                'tasks' => [] // Will fill this with task details below
            ];
            
            // Group logs by task
            $taskIds = $userLogs->pluck('task_id')->unique();
            foreach ($taskIds as $taskId) {
                $taskLogs = $userLogs->where('task_id', $taskId);
                
                // Only process tasks that have associated logs
                if ($taskLogs->isNotEmpty()) {
                    $task = $taskLogs->first()->task;
                    
                    if ($task) {
                        $userTotals[$member->id]['tasks'][$taskId] = [
                            'task' => $task,
                            'total_minutes' => $taskLogs->sum('minutes'),
                            'formatted_total' => $this->formatMinutes($taskLogs->sum('minutes'))
                        ];
                    }
                }
            }
        }
        
        // Calculate project total
        $projectTotal = $timeLogs->sum('minutes');
        $formattedProjectTotal = $this->formatMinutes($projectTotal);

        // Load other project data
        $project->load(['tasks' => function ($query) {
            $query->with(['status', 'type', 'priority', 'assignee']);
        }, 'taskStatuses' => function ($query) {
            $query->orderBy('order');
        }, 'members', 'lead', 'department']);

        return view('admin.projects.show', compact(
            'project',
            'userTotals',
            'projectTotal',
            'formattedProjectTotal',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Format minutes as hours and minutes
     */
    private function formatMinutes($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        $result = '';
        if ($hours > 0) {
            $result .= $hours . 'h ';
        }
        if ($mins > 0 || $hours == 0) {
            $result .= $mins . 'm';
        }
        
        return trim($result);
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