<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $projects = $user->projects()->get();
        $assignedTasks = $user->assignedTasks()->with(['project', 'status', 'priority'])->get();
        
        return view('home', compact('projects', 'assignedTasks'));
    }
}