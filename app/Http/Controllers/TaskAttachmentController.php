<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Traits\LogsUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    use LogsUserActivity;

    /**
     * Store a new attachment for a task.
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
            'attachment' => 'required|file|max:10240', // 10MB max file size
        ]);
        
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $file = $request->file('attachment');
            $filename = $file->getClientOriginalName();
            $filePath = $file->store('task-attachments/' . $task->id, 'public');
            
            $attachment = TaskAttachment::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
            
            // Log activity
            $this->logUserActivity('Added attachment to task: ' . $task->task_number);
            
            return redirect()->route('projects.tasks.show', [$project, $task])
                ->with('success', 'File uploaded successfully.');
        }
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('error', 'Failed to upload file.');
    }

    /**
     * Download an attachment.
     */
    public function download(Project $project, Task $task, TaskAttachment $attachment)
    {
        // Check if the task belongs to the project
        if ($task->project_id !== $project->id || $attachment->task_id !== $task->id) {
            abort(404);
        }
        
        // Check if the user can view this project
        $this->authorize('view', $project);
        
        return Storage::disk('public')->download(
            $attachment->file_path, 
            $attachment->filename
        );
    }

    /**
     * Delete an attachment.
     */
    public function destroy(Project $project, Task $task, TaskAttachment $attachment)
    {
        // Check if the task belongs to the project
        if ($task->project_id !== $project->id || $attachment->task_id !== $task->id) {
            abort(404);
        }
        
        // Check if the user is authorized to delete the attachment
        $this->authorize('view', $project);
        
        // Additional check: only attachment owner or project lead/admin can delete
        if ($attachment->user_id !== Auth::id() && $project->lead_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Delete the file
        Storage::disk('public')->delete($attachment->file_path);
        
        // Delete the attachment record
        $attachment->delete();
        
        // Log activity
        $this->logUserActivity('Deleted attachment from task: ' . $task->task_number);
        
        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Attachment deleted successfully.');
    }
}