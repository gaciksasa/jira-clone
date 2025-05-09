<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignedTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Get all tasks assigned to the user that are subtasks (have parent_id)
        $subtasks = Task::where('assignee_id', $user->id)
            ->whereNotNull('parent_id')
            ->with(['parent', 'project', 'status', 'type', 'priority'])
            ->get()
            ->groupBy(function($task) {
                return $task->closed_at ? 'completed' : 'incomplete';
            });
        
        // Changed the view path to match your structure
        return view('projects.tasks.assigned', compact('subtasks'));
    }
}