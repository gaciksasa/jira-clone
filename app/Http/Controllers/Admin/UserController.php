<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogsUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use App\Models\TimeLog;

class UserController extends Controller
{
    use LogsUserActivity;

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        // Check if user has permission to manage users
        $this->authorize('manage users');

        // Start with a base query
        $query = User::query()->with('roles', 'department');
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Filter by department if selected
        if ($request->has('department_id') && !empty($request->department_id)) {
            $query->where('department_id', $request->department_id);
        }
        
        // Filter by role if selected
        if ($request->has('role_id') && !empty($request->role_id)) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('id', $request->role_id);
            });
        }
        
        // Filter by status if selected
        if ($request->has('status') && !empty($request->status)) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }
        
        // Handle sorting
        $sortField = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['id', 'name', 'email', 'is_active', 'department'];
        
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name'; // Default sort field
        }
        
        // Special handling for department sorting
        if ($sortField === 'department') {
            // Join with departments table and sort by department name
            $query->leftJoin('departments', 'users.department_id', '=', 'departments.id')
                ->select('users.*')
                ->orderBy('departments.name', $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            // Regular column sorting
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }
        
        // Get paginated results
        $users = $query->paginate(10)->withQueryString();
        
        // Get all departments and roles for filter dropdowns
        $departments = \App\Models\Department::orderBy('name')->get();
        $roles = \Spatie\Permission\Models\Role::all();
        
        return view('admin.users.index', compact('users', 'departments', 'roles', 'sortField', 'sortDirection'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        // Check if user has permission to manage users
        $this->authorize('manage users');

        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to manage users
        $this->authorize('manage users');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array|nullable',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'boolean',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->is_active ?? true,
            'department_id' => $request->department_id,
        ]);

        // Assign roles if provided
        if ($request->has('roles') && !empty($request->roles)) {
            // Convert role IDs to role objects before assigning
            $roles = collect($request->roles)->map(function ($id) {
                return Role::findById($id);
            });
            $user->syncRoles($roles);
        }

        // Log activity
        $this->logUserActivity('Created user: ' . $user->name);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Check if user has permission to manage users
        $this->authorize('manage users');

        // Load the user data with relationships
        $user->load('roles');
        
        // Get time report data similar to TimeReportController
        
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
        $startDate = request()->get('start_date') 
            ? Carbon::createFromFormat('Y-m-d', request()->get('start_date'))
            : Carbon::now()->startOfMonth();
            
        $endDate = request()->get('end_date') 
            ? Carbon::createFromFormat('Y-m-d', request()->get('end_date'))
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
        
        // Get additional data for the user view
        $assignedTasks = $user->assignedTasks()
            ->with(['project', 'status', 'type', 'priority'])
            ->get();

        // Get projects the user is a member of
        $userProjects = $user->projects()->get();
        
        // Count projects the user is a member of
        $projectsCount = $user->projects()->count();

        // Count tasks - use the already loaded assignedTasks collection
        $tasksCount = $assignedTasks->count();
        
        return view('admin.users.show', compact(
            'user', 
            'assignedTasks',
            'thisWeekTotal',
            'thisMonthTotal',
            'thisYearTotal',
            'projectTotals',
            'userTotal',
            'formattedUserTotal',
            'startDate',
            'endDate',
            'projectsCount',
            'userProjects'
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

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        // Check if user has permission to manage users
        $this->authorize('manage users');

        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        // Check if user has permission to manage users
        $this->authorize('manage users');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array|nullable',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'boolean',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $request->department_id,
        ];

        // Only update is_active if it's not the current user
        if ($user->id !== auth()->id()) {
            $userData['is_active'] = $request->has('is_active') ? true : false;
        }

        // Update password only if provided
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        // Sync roles
        if ($request->has('roles')) {
            // Convert role IDs to role objects before assigning
            $roles = collect($request->roles)->map(function ($id) {
                return Role::findById($id);
            });
            $user->syncRoles($roles);
        } else {
            $user->syncRoles([]);
        }

        // Log activity
        $this->logUserActivity('Updated user: ' . $user->name);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Check if user has permission to manage users
        $this->authorize('manage users');

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Log activity before deletion
        $this->logUserActivity('Deleted user: ' . $user->name);
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(User $user)
    {
        // Check if user has permission to manage users
        $this->authorize('manage users');

        // Prevent toggling own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        
        // Log activity
        $this->logUserActivity($status . ' user: ' . $user->name);
        
        return redirect()->route('admin.users.index')
            ->with('success', "User {$status} successfully.");
    }
}