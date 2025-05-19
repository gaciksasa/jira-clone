<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TaskUnassigned extends Notification
{
    use Queueable;

    protected $task;
    protected $unassignedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, $unassignedBy)
    {
        $this->task = $task;
        $this->unassignedBy = $unassignedBy;
        
        Log::info('TaskUnassigned notification created', [
            'task' => $task->task_number
        ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Get the link with full URL
        $link = route('projects.tasks.show', [
            'project' => $this->task->project_id,
            'task' => $this->task->id
        ]);
        
        // Prepare the notification data
        $data = [
            'task_id' => $this->task->id,
            'task_number' => $this->task->task_number,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project->name,
            'unassigned_by' => $this->unassignedBy->name,
            'unassigned_by_id' => $this->unassignedBy->id,
            'message' => 'You have been unassigned from task ' . $this->task->task_number . ' by ' . $this->unassignedBy->name,
            'link' => $link
        ];
        
        Log::info('TaskUnassigned notification data prepared', [
            'task' => $this->task->task_number,
            'link' => $link
        ]);
        
        return $data;
    }
}