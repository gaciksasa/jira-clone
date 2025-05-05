<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimesheetController extends Controller
{
    /**
     * Display the user's timesheet for the current month
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get month and year from request or use current month
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        
        // Create date objects
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        // Create a period for all days in the month
        $period = CarbonPeriod::create($startDate, $endDate);
        $days = collect($period->toArray());
        
        // Get all time logs for this month
        $timeLogs = TimeLog::where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->with(['task', 'task.project'])
            ->get();
        
        // Group logs by task ID
        $taskIds = $timeLogs->pluck('task_id')->unique();
        
        // Get all tasks with time logs this month
        $tasks = $user->assignedTasks()
            ->whereIn('id', $taskIds)
            ->with(['project', 'status', 'type', 'priority'])
            ->get();
            
        // Add tasks that had no logs but user worked on before
        $previousTaskIds = TimeLog::where('user_id', $user->id)
            ->whereNotIn('task_id', $taskIds)
            ->where('work_date', '<', $startDate)
            ->orderBy('work_date', 'desc')
            ->take(5) // Get the 5 most recent tasks
            ->pluck('task_id')
            ->unique();
            
        $previousTasks = $user->assignedTasks()
            ->whereIn('id', $previousTaskIds)
            ->with(['project', 'status', 'type', 'priority'])
            ->get();
            
        $tasks = $tasks->merge($previousTasks)->unique('id');
        
        // Prepare a task logs matrix for the view
        // [task_id => [date => minutes]]
        $taskLogsMatrix = [];
        
        foreach ($tasks as $task) {
            $taskLogsMatrix[$task->id] = [];
            
            // Initialize with zero minutes for all days
            foreach ($days as $day) {
                $taskLogsMatrix[$task->id][$day->format('Y-m-d')] = 0;
            }
            
            // Fill in the actual logged minutes
            $taskLogs = $timeLogs->where('task_id', $task->id);
            foreach ($taskLogs as $log) {
                $date = $log->work_date->format('Y-m-d');
                if (isset($taskLogsMatrix[$task->id][$date])) {
                    $taskLogsMatrix[$task->id][$date] += $log->minutes;
                }
            }
        }
        
        // Calculate daily totals
        $dailyTotals = [];
        foreach ($days as $day) {
            $date = $day->format('Y-m-d');
            $dailyTotals[$date] = $timeLogs->where('work_date', $date)->sum('minutes');
        }
        
        // Calculate monthly total
        $monthlyTotal = $timeLogs->sum('minutes');
        $formattedMonthlyTotal = self::formatMinutes($monthlyTotal);
        
        // Calculate task totals
        $taskTotals = [];
        foreach ($tasks as $task) {
            $taskTotals[$task->id] = $timeLogs->where('task_id', $task->id)->sum('minutes');
        }
        
        // Available months for the selector
        $availableMonths = [];
        $earliestLog = TimeLog::where('user_id', $user->id)->orderBy('work_date', 'asc')->first();
        
        if ($earliestLog) {
            $earliestDate = $earliestLog->work_date;
            $currentDate = Carbon::now();
            
            // Add all months from earliest log to current month
            $monthIterator = Carbon::createFromDate($earliestDate->year, $earliestDate->month, 1);
            
            while ($monthIterator->lte($currentDate)) {
                $availableMonths[] = [
                    'year' => $monthIterator->year,
                    'month' => $monthIterator->month,
                    'name' => $monthIterator->format('F Y'),
                ];
                $monthIterator->addMonth();
            }
        } else {
            // If no logs yet, just show current month
            $currentDate = Carbon::now();
            $availableMonths[] = [
                'year' => $currentDate->year,
                'month' => $currentDate->month,
                'name' => $currentDate->format('F Y'),
            ];
        }
        
        // Reverse to show latest months first
        $availableMonths = array_reverse($availableMonths);
        
        return view('timesheet.index', compact(
            'tasks', 
            'days', 
            'taskLogsMatrix', 
            'dailyTotals', 
            'monthlyTotal',
            'formattedMonthlyTotal',
            'taskTotals',
            'year',
            'month',
            'availableMonths'
        ));
    }
    
    /**
     * Add or update time log through AJAX
     */
    public function updateTime(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'date' => 'required|date',
            'minutes' => 'required|integer|min:0|max:1440', // Max 24 hours
        ]);
        
        $user = Auth::user();
        $taskId = $request->task_id;
        $date = $request->date;
        $minutes = $request->minutes;
        
        // Check if user can log time to this task (is assignee)
        $task = $user->assignedTasks()->findOrFail($taskId);
        
        // Find or create time log
        $timeLog = TimeLog::where('user_id', $user->id)
            ->where('task_id', $taskId)
            ->where('work_date', $date)
            ->first();
            
        if ($minutes > 0) {
            if ($timeLog) {
                // Update existing log
                $timeLog->update([
                    'minutes' => $minutes,
                ]);
            } else {
                // Create new log
                $timeLog = TimeLog::create([
                    'user_id' => $user->id,
                    'task_id' => $taskId,
                    'work_date' => $date,
                    'minutes' => $minutes,
                    'description' => 'Time logged from timesheet',
                ]);
            }
        } else if ($timeLog) {
            // Delete log if minutes is 0
            $timeLog->delete();
        }
        
        // Recalculate daily total
        $dailyTotal = TimeLog::where('user_id', $user->id)
            ->where('work_date', $date)
            ->sum('minutes');
            
        // Recalculate task total
        $taskTotal = TimeLog::where('user_id', $user->id)
            ->where('task_id', $taskId)
            ->sum('minutes');
            
        // Recalculate monthly total
        $month = Carbon::parse($date)->month;
        $year = Carbon::parse($date)->year;
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        $monthlyTotal = TimeLog::where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->sum('minutes');
            
        // Format times for display
        $formattedDailyTotal = self::formatMinutes($dailyTotal);
        $formattedTaskTotal = self::formatMinutes($taskTotal);
        $formattedMonthlyTotal = self::formatMinutes($monthlyTotal);
        
        return response()->json([
            'success' => true,
            'dailyTotal' => $dailyTotal,
            'taskTotal' => $taskTotal,
            'monthlyTotal' => $monthlyTotal,
            'formattedDailyTotal' => $formattedDailyTotal,
            'formattedTaskTotal' => $formattedTaskTotal,
            'formattedMonthlyTotal' => $formattedMonthlyTotal,
        ]);
    }
    
    /**
     * Format minutes as hours and minutes - static version for blade templates
     */
    public static function formatMinutes($minutes)
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