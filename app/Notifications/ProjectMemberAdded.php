<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ProjectMemberAdded extends Notification
{
    use Queueable;

    protected $project;
    protected $addedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project, User $addedBy)
    {
        $this->project = $project;
        $this->addedBy = $addedBy;
        
        Log::info('ProjectMemberAdded notification created', [
            'project' => $project->name,
            'added_by' => $addedBy->name
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
        $link = route('projects.show', $this->project);
        
        // Prepare the notification data
        $data = [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'added_by' => $this->addedBy->name,
            'added_by_id' => $this->addedBy->id,
            'message' => 'You have been added to project ' . $this->project->name . ' by ' . $this->addedBy->name,
            'link' => $link
        ];
        
        Log::info('ProjectMemberAdded notification data prepared', [
            'project' => $this->project->name,
            'link' => $link
        ]);
        
        return $data;
    }
}