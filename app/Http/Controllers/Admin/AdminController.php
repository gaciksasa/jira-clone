<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display admin dashboard.
     */
    public function dashboard()
    {
        // Check if user has admin role
        $this->authorize('manage users');

        $userCount = User::count();
        $projectCount = Project::count();
        $taskCount = Task::count();
        $departmentCount = Department::count();
        $recentUsers = User::latest()->take(5)->get();

        $project = null;

        return view('admin.dashboard', compact(
            'userCount',
            'projectCount',
            'taskCount',
            'departmentCount',
            'recentUsers',
            'project'
        ));
    }
}