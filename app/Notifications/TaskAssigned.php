<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('projects.tasks.show', [$this->task->project, $this->task]);
        
        return (new MailMessage)
                    ->subject('Task Assigned to You: ' . $this->task->task_number)
                    ->line('You have been assigned a task by ' . $this->assignedBy->name)
                    ->line('Task: ' . $this->task->title)
                    ->line('Project: ' . $this->task->project->name)
                    ->action('View Task', $url)
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_number' => $this->task->task_number,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project->name,
            'assigned_by' => $this->assignedBy->name,
            'assigned_by_id' => $this->assignedBy->id,
            'message' => 'You have been assigned a task by ' . $this->assignedBy->name,
            'link' => route('projects.tasks.show', [$this->task->project, $this->task])
        ];
    }
}