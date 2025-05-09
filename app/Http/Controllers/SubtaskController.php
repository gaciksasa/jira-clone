<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Subtask;
use App\Traits\LogsUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubtaskController extends Controller
{
    use LogsUserActivity;

    public function index(Project $project, Task $task)
    {
        // Check if the task belongs to the project
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        $subtasks = $task->subtasks()->with('assignee')->orderBy('order')->get();
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'subtasks' => $subtasks
            ]);
        }
        
        return view('projects.tasks.subtasks.index', compact('project', 'task', 'subtasks'));
    }

    /**
     * Store a newly created subtask in storage.
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        // Calculate the order (should be max order + 1)
        $maxOrder = $task->subtasks()->max('order') ?? 0;
        
        $subtask = $task->subtasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'assignee_id' => $request->assignee_id,
            'order' => $maxOrder + 1,
        ]);
        
        // Log activity
        $this->logUserActivity('Added subtask to task: ' . $task->task_number);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'subtask' => $subtask->load('assignee'),
                'message' => 'Subtask created successfully'
            ]);
        }
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Subtask created successfully.');
    }

    /**
     * Update the specified subtask in storage.
     */
    public function update(Request $request, Project $project, Task $task, Subtask $subtask)
    {
        // Check if the task belongs to the project and subtask belongs to the task
        if ($task->project_id !== $project->id || $subtask->task_id !== $task->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
        ]);
        
        $subtask->update([
            'title' => $request->title,
            'description' => $request->description,
            'assignee_id' => $request->assignee_id,
        ]);
        
        // Log activity
        $this->logUserActivity('Updated subtask on task: ' . $task->task_number);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'subtask' => $subtask->fresh(['assignee']),
                'message' => 'Subtask updated successfully'
            ]);
        }
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Subtask updated successfully.');
    }

    /**
     * Remove the specified subtask from storage.
     */
    public function destroy(Project $project, Task $task, Subtask $subtask)
    {
        // Check if the task belongs to the project and subtask belongs to the task
        if ($task->project_id !== $project->id || $subtask->task_id !== $task->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        $subtask->delete();
        
        // Re-order remaining subtasks
        $task->subtasks()
            ->orderBy('order')
            ->get()
            ->each(function ($item, $index) {
                $item->update(['order' => $index + 1]);
            });
        
        // Log activity
        $this->logUserActivity('Deleted subtask from task: ' . $task->task_number);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Subtask deleted successfully'
            ]);
        }
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Subtask deleted successfully.');
    }

    /**
     * Toggle subtask completion status.
     */
    public function toggleComplete(Project $project, Task $task, Subtask $subtask)
    {
        // Check if the task belongs to the project and subtask belongs to the task
        if ($task->project_id !== $project->id || $subtask->task_id !== $task->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        if ($subtask->is_completed) {
            $subtask->incomplete();
            $actionText = 'marked as incomplete';
        } else {
            $subtask->complete();
            $actionText = 'completed';
        }
        
        // Log activity
        $this->logUserActivity('Subtask ' . $actionText . ' on task: ' . $task->task_number);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'subtask' => $subtask->fresh(),
                'completed_count' => $task->completedSubtasksCount(),
                'total_count' => $task->subtasks()->count(),
                'percentage' => $task->subtaskCompletionPercentage(),
                'message' => 'Subtask status updated successfully'
            ]);
        }
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Subtask ' . $actionText . ' successfully.');
    }

    /**
     * Reorder subtasks.
     */
    public function reorder(Request $request, Project $project, Task $task)
    {
        // Check if the task belongs to the project
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        $request->validate([
            'subtasks' => 'required|array',
            'subtasks.*' => 'exists:subtasks,id',
        ]);
        
        // Update the order of subtasks
        foreach ($request->subtasks as $index => $subtaskId) {
            // Verify that the subtask belongs to this task
            $subtask = Subtask::find($subtaskId);
            if ($subtask && $subtask->task_id === $task->id) {
                $subtask->update(['order' => $index + 1]);
            }
        }
        
        // Log activity
        $this->logUserActivity('Reordered subtasks for task: ' . $task->task_number);
        
        return response()->json(['success' => true]);
    }

    public function assignedToMe()
    {
        $user = Auth::user();
        
        $subtasks = Subtask::with(['task.project', 'task.status', 'task.type', 'task.priority'])
            ->where('assignee_id', $user->id)
            ->get()
            ->groupBy('is_completed');
        
        return view('subtasks.assigned_to_me', compact('subtasks'));
    }
}