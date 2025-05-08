<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TimeLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeReportController extends Controller
{
    /**
     * Display the reports dashboard
     */
    public function index(Request $request)
    {
        // Check if user has permission to manage users or projects
        $this->authorize('manage users');
        
        // Determine which user's report to display
        $user = Auth::user();
        if ($request->has('user_id') && !empty($request->user_id)) {
            // Allow admins to view other users' reports
            $user = User::findOrFail($request->user_id);
        }
        
        // Calculate this week's total
        $thisWeekTotal = TimeLog::where('user_id', $user->id)
            ->whereBetween('work_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('minutes');
        
        // Calculate this month's total
        $thisMonthTotal = TimeLog::where('user_id', $user->id)
            ->whereBetween('work_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('minutes');
        
        // Calculate this year's total
        $thisYearTotal = TimeLog::where('user_id', $user->id)
            ->whereBetween('work_date', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])
            ->sum('minutes');
        
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
        
        // Get all time logs for this user
        $timeLogs = TimeLog::where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->with(['task', 'task.project'])
            ->get();
        
        // Group by project
        $projectTotals = [];
        $projects = $user->projects;
        
        foreach ($projects as $project) {
            $projectLogs = $timeLogs->filter(function ($log) use ($project) {
                return $log->task->project_id === $project->id;
            });
            
            if ($projectLogs->count() > 0) {
                $projectTotals[$project->id] = [
                    'project' => $project,
                    'total_minutes' => $projectLogs->sum('minutes'),
                    'formatted_total' => $this->formatMinutes($projectLogs->sum('minutes')),
                    'tasks' => [] // Will fill this with task details below
                ];
                
                // Group logs by task
                $taskIds = $projectLogs->pluck('task_id')->unique();
                foreach ($taskIds as $taskId) {
                    $taskLogs = $projectLogs->where('task_id', $taskId);
                    $task = $taskLogs->first()->task;
                    
                    $projectTotals[$project->id]['tasks'][$taskId] = [
                        'task' => $task,
                        'total_minutes' => $taskLogs->sum('minutes'),
                        'formatted_total' => $this->formatMinutes($taskLogs->sum('minutes'))
                    ];
                }
            }
        }
        
        // Calculate user total
        $userTotal = $timeLogs->sum('minutes');
        $formattedUserTotal = $this->formatMinutes($userTotal);
        
        // Flag to indicate if we're viewing another user's report
        $viewingOtherUser = Auth::id() !== $user->id;
        
        return view('admin.reports.index', compact(
            'thisWeekTotal',
            'thisMonthTotal',
            'thisYearTotal',
            'projectTotals',
            'userTotal',
            'formattedUserTotal',
            'startDate',
            'endDate',
            'user',
            'viewingOtherUser'
        ));
    }

    /**
     * Display project time report for all users
     */
    public function project(Request $request, Project $project)
    {
        // Check if the user is authorized to view this project
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
                $task = $taskLogs->first()->task;
                
                $userTotals[$member->id]['tasks'][$taskId] = [
                    'task' => $task,
                    'total_minutes' => $taskLogs->sum('minutes'),
                    'formatted_total' => $this->formatMinutes($taskLogs->sum('minutes'))
                ];
            }
        }
        
        // Calculate project total
        $projectTotal = $timeLogs->sum('minutes');
        $formattedProjectTotal = $this->formatMinutes($projectTotal);
        
        return view('admin.reports.project-time', compact(
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
}