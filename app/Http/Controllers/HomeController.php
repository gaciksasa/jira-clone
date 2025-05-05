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
        
        // Apply sorting for open tasks
        $openSortField = $request->get('open_sort_by', 'priority_id');
        $openSortDirection = $request->get('open_sort_direction', 'asc');
        
        // Apply sorting for closed tasks
        $closedSortField = $request->get('closed_sort_by', 'closed_at');
        $closedSortDirection = $request->get('closed_sort_direction', 'desc');
        
        // Ensure the sort fields are valid
        $allowedSortFields = ['title', 'task_number', 'updated_at', 'created_at', 'priority_id', 'task_status_id'];
        
        if (!in_array($openSortField, $allowedSortFields)) {
            $openSortField = 'priority_id';
        }
        
        if (!in_array($closedSortField, $allowedSortFields) && $closedSortField !== 'closed_at') {
            $closedSortField = 'closed_at';
        }
        
        $openTasksQuery->orderBy($openSortField, $openSortDirection);
        $closedTasksQuery->orderBy($closedSortField, $closedSortDirection);
        
        // Get time statistics
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();
        
        // Get time logs for different periods
        $todayMinutes = \App\Models\TimeLog::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->sum('minutes');
            
        $yesterdayMinutes = \App\Models\TimeLog::where('user_id', $user->id)
            ->whereDate('work_date', $yesterday)
            ->sum('minutes');
            
        $thisWeekMinutes = \App\Models\TimeLog::where('user_id', $user->id)
            ->whereBetween('work_date', [$weekStart, $weekEnd])
            ->sum('minutes');
            
        $lastWeekMinutes = \App\Models\TimeLog::where('user_id', $user->id)
            ->whereBetween('work_date', [$lastWeekStart, $lastWeekEnd])
            ->sum('minutes');
        
        // Format time values
        $formattedTodayMinutes = $this->formatMinutes($todayMinutes);
        $formattedYesterdayMinutes = $this->formatMinutes($yesterdayMinutes);
        $formattedThisWeekMinutes = $this->formatMinutes($thisWeekMinutes);
        $formattedLastWeekMinutes = $this->formatMinutes($lastWeekMinutes);
        
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
            'closedSortDirection',
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