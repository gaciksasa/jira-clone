<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Get tasks for a project that the user has access to.
     */
    public function index(Request $request, Project $project)
    {
        // Check if user is a member of the project
        if (!$project->members->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Get open tasks for this project
        $tasks = $project->tasks()
            ->whereNull('closed_at')
            ->select('id', 'title', 'task_number')
            ->orderBy('task_number', 'desc')
            ->get();
            
        return response()->json($tasks);
    }
}