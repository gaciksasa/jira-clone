<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ProjectMemberRemoved extends Notification
{
    use Queueable;

    protected $project;
    protected $removedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project, User $removedBy)
    {
        $this->project = $project;
        $this->removedBy = $removedBy;
        
        Log::info('ProjectMemberRemoved notification created', [
            'project' => $project->name,
            'removed_by' => $removedBy->name
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
        // Prepare the notification data
        $data = [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'removed_by' => $this->removedBy->name,
            'removed_by_id' => $this->removedBy->id,
            'message' => 'You have been removed from project ' . $this->project->name . ' by ' . $this->removedBy->name,
            'link' => route('home')
        ];
        
        Log::info('ProjectMemberRemoved notification data prepared', [
            'project' => $this->project->name
        ]);
        
        return $data;
    }
}