<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimeLog;
use App\Traits\LogsUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeLogController extends Controller
{
    use LogsUserActivity;

    /**
     * Store a new time log for a task.
     */
    public function store(Request $request, Project $project, Task $task)
    {
        // Check if the task belongs to the project
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        $request->validate([
            'minutes' => 'required|integer|min:1',
            'work_date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string',
        ]);
        
        $timeLog = TimeLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'minutes' => $request->minutes,
            'work_date' => $request->work_date,
            'description' => $request->description,
        ]);
        
        // Log activity
        $this->logUserActivity('Logged ' . $timeLog->formattedTime() . ' of work on task: ' . $task->task_number);
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Time logged successfully.');
    }

    /**
     * Delete a time log entry.
     */
    public function destroy(Project $project, Task $task, TimeLog $timeLog)
    {
        // Check if the task belongs to the project
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the time log belongs to the task
        if ($timeLog->task_id !== $task->id) {
            abort(404);
        }
        
        // Check if the user can delete this time log (only the creator or admins)
        if ($timeLog->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Log activity before deletion
        $this->logUserActivity('Deleted time log (' . $timeLog->formattedTime() . ') from task: ' . $task->task_number);
        
        $timeLog->delete();
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Time log deleted successfully.');
    }
}