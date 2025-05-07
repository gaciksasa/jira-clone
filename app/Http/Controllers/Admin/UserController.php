<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogsUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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
        
        // Get paginated results
        $users = $query->paginate(10);
        
        // Get all departments and roles for filter dropdowns
        $departments = \App\Models\Department::orderBy('name')->get();
        $roles = \Spatie\Permission\Models\Role::all();
        
        return view('admin.users.index', compact('users', 'departments', 'roles'));
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

        $user->load('roles');
        return view('admin.users.show', compact('user'));
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