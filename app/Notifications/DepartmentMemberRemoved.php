<?php

namespace App\Notifications;

use App\Models\Department;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class DepartmentMemberRemoved extends Notification
{
    use Queueable;

    protected $department;
    protected $removedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Department $department, User $removedBy)
    {
        $this->department = $department;
        $this->removedBy = $removedBy;
        
        Log::info('DepartmentMemberRemoved notification created', [
            'department' => $department->name,
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
            'department_id' => $this->department->id,
            'department_name' => $this->department->name,
            'removed_by' => $this->removedBy->name,
            'removed_by_id' => $this->removedBy->id,
            'message' => 'You have been removed from the ' . $this->department->name . ' department by ' . $this->removedBy->name
        ];
        
        Log::info('DepartmentMemberRemoved notification data prepared', [
            'department' => $this->department->name
        ]);
        
        return $data;
    }
}