<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TaskAssigned extends Notification
{
    use Queueable;

    protected $task;
    protected $assignedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, $assignedBy)
    {
        $this->task = $task;
        $this->assignedBy = $assignedBy;
        
        Log::info('TaskAssigned notification created', [
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
        // IMPORTANT: Make sure 'database' is returned
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
            'assigned_by' => $this->assignedBy->name,
            'assigned_by_id' => $this->assignedBy->id,
            'message' => 'You have been assigned task ' . $this->task->task_number . ' by ' . $this->assignedBy->name,
            'link' => $link
        ];
        
        Log::info('TaskAssigned notification data prepared', [
            'task' => $this->task->task_number,
            'link' => $link
        ]);
        
        return $data;
    }
}