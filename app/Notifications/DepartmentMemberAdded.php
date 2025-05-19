<?php

namespace App\Notifications;

use App\Models\Department;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class DepartmentMemberAdded extends Notification
{
    use Queueable;

    protected $department;
    protected $addedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Department $department, User $addedBy)
    {
        $this->department = $department;
        $this->addedBy = $addedBy;
        
        Log::info('DepartmentMemberAdded notification created', [
            'department' => $department->name,
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
        $link = route('departments.show', $this->department);
        
        // Prepare the notification data
        $data = [
            'department_id' => $this->department->id,
            'department_name' => $this->department->name,
            'added_by' => $this->addedBy->name,
            'added_by_id' => $this->addedBy->id,
            'message' => 'You have been added to the ' . $this->department->name . ' department by ' . $this->addedBy->name
        ];
        
        Log::info('DepartmentMemberAdded notification data prepared', [
            'department' => $this->department->name
        ]);
        
        return $data;
    }
}