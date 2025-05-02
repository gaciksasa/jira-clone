<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Traits\LogsUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    use LogsUserActivity;

    /**
     * Display a listing of departments.
     */
    public function index()
    {
        // Check if user has permission to manage departments
        $this->authorize('manage departments');

        $departments = Department::withCount('projects')->get();
        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        // Check if user has permission to manage departments
        $this->authorize('manage departments');

        return view('admin.departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to manage departments
        $this->authorize('manage departments');

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|alpha_num|unique:departments',
            'description' => 'nullable|string',
        ]);

        $department = Department::create([
            'name' => $request->name,
            'code' => Str::upper($request->code),
            'description' => $request->description,
        ]);

        // Log activity
        $this->logUserActivity('Created department: ' . $department->name);
        
        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        // Check if user has permission to manage departments
        $this->authorize('manage departments');

        $projects = $department->projects()->withCount('tasks')->get();
        
        return view('admin.departments.show', compact('department', 'projects'));
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        // Check if user has permission to manage departments
        $this->authorize('manage departments');
        
        return view('admin.departments.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, Department $department)
    {
        // Check if user has permission to manage departments
        $this->authorize('manage departments');

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|alpha_num|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
        ]);

        $department->update([
            'name' => $request->name,
            'code' => Str::upper($request->code),
            'description' => $request->description,
        ]);

        // Log activity
        $this->logUserActivity('Updated department: ' . $department->name);
        
        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department)
    {
        // Check if user has permission to manage departments
        $this->authorize('manage departments');

        // Check if department has projects
        if ($department->projects()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', 'Cannot delete department with associated projects.');
        }

        // Log activity before deletion
        $this->logUserActivity('Deleted department: ' . $department->name);
        
        $department->delete();
        
        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}