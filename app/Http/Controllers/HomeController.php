<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Priority;
use App\Models\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Base query for assigned tasks - shared conditions
        $baseQuery = $user->assignedTasks()
            ->with(['project', 'status', 'priority', 'type']);
        
        // Filter by project if selected
        if ($request->has('project_id') && !empty($request->project_id)) {
            $baseQuery->where('project_id', $request->project_id);
        }
        
        // Search by title or task number
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('task_number', 'like', "%{$search}%");
            });
        }
        
        // Create separate queries for open and closed tasks
        $openTasksQuery = clone $baseQuery;
        $closedTasksQuery = clone $baseQuery;
        
        // Filter by task state
        $openTasksQuery->whereNull('closed_at');
        $closedTasksQuery->whereNotNull('closed_at');
        
        // Apply sorting for open tasks
        $openSortField = $request->get('open_sort_by', 'priority_id');
        $openSortDirection = $request->get('open_sort_direction', 'asc');
        
        // Apply sorting for closed tasks
        $closedSortField = $request->get('closed_sort_by', 'closed_at');
        $closedSortDirection = $request->get('closed_sort_direction', 'desc');
        
        // Ensure the sort fields are valid
        $allowedSortFields = ['title', 'task_number', 'updated_at', 'created_at', 'priority_id', 'task_status_id', 'task_type_id', 'project_id'];
        
        if (!in_array($openSortField, $allowedSortFields)) {
            $openSortField = 'priority_id';
        }
        
        if (!in_array($closedSortField, $allowedSortFields) && $closedSortField !== 'closed_at') {
            $closedSortField = 'closed_at';
        }
        
        $openTasksQuery->orderBy($openSortField, $openSortDirection);
        $closedTasksQuery->orderBy($closedSortField, $closedSortDirection);
        
        // Get all user's projects for the filter dropdown
        $projects = $user->projects()->get();
        $priorities = Priority::orderBy('order')->get();
        $statuses = TaskStatus::distinct('name')->get();
        
        // Get tasks
        $openTasks = $openTasksQuery->paginate(10, ['*'], 'open_page');
        $closedTasks = $closedTasksQuery->paginate(5, ['*'], 'closed_page');
        
        return view('home', compact(
            'projects', 
            'openTasks', 
            'closedTasks', 
            'priorities', 
            'statuses',
            'openSortField', 
            'openSortDirection',
            'closedSortField',
            'closedSortDirection'
        ));
    }
}