<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Priority;
use App\Models\TaskStatus;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
        
        // Apply sorting if requested, defaulting to priority for open tasks
        $sortField = $request->get('sort_by', 'priority_id');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        // Ensure the sort field is valid
        $allowedSortFields = ['title', 'task_number', 'updated_at', 'created_at', 'priority_id', 'task_status_id'];
        
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'priority_id';
        }
        
        $openTasksQuery->orderBy($sortField, $sortDirection);
        $closedTasksQuery->orderBy('closed_at', 'desc'); // Always sort closed tasks by close date
        
        // Get all user's projects for the filter dropdown
        $projects = $user->projects()->get();
        $priorities = Priority::orderBy('order')->get();
        $statuses = TaskStatus::distinct('name')->get();
        
        // Get tasks
        $openTasks = $openTasksQuery->paginate(10, ['*'], 'open_page');
        $closedTasks = $closedTasksQuery->paginate(5, ['*'], 'closed_page');
        
        // Get time tracking data for dashboard
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeek = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
        $lastWeek = [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()];
        
        // Get time logs
        $todayMinutes = TimeLog::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->sum('minutes');
            
        $yesterdayMinutes = TimeLog::where('user_id', $user->id)
            ->whereDate('work_date', $yesterday)
            ->sum('minutes');
            
        $thisWeekMinutes = TimeLog::where('user_id', $user->id)
            ->whereBetween('work_date', $thisWeek)
            ->sum('minutes');
            
        $lastWeekMinutes = TimeLog::where('user_id', $user->id)
            ->whereBetween('work_date', $lastWeek)
            ->sum('minutes');
        
        // Format time for display using a helper function instead of referencing another controller
        $formattedTodayMinutes = $this->formatMinutes($todayMinutes);
        $formattedYesterdayMinutes = $this->formatMinutes($yesterdayMinutes);
        $formattedThisWeekMinutes = $this->formatMinutes($thisWeekMinutes);
        $formattedLastWeekMinutes = $this->formatMinutes($lastWeekMinutes);
        
        return view('home', compact(
            'projects', 
            'openTasks', 
            'closedTasks', 
            'priorities', 
            'statuses',
            'formattedTodayMinutes',
            'formattedYesterdayMinutes',
            'formattedThisWeekMinutes',
            'formattedLastWeekMinutes'
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
}