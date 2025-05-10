<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'avatar',
        'department_id',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Existing relationships
    public function leadProjects()
    {
        return $this->hasMany(Project::class, 'lead_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function assignedSubtasks()
    {
        return $this->assignedTasks()->whereNotNull('parent_id');
    }

    public function reportedTasks()
    {
        return $this->hasMany(Task::class, 'reporter_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }
    
    public function canMoveAnyTask()
    {
        return $this->hasPermissionTo('manage projects') || $this->hasPermissionTo('change status');
    }
        
    public function canMoveTask(Task $task)
    {
        return $this->canMoveAnyTask() || $this->id === $task->assignee_id;
    }

    public function vacationBalances()
    {
        return $this->hasMany(UserVacationBalance::class);
    }

    public function vacationRequests()
    {
        return $this->hasMany(VacationRequest::class);
    }

    public function pendingApprovals()
    {
        return $this->hasMany(VacationRequest::class, 'approver_id')
                    ->where('status', 'pending');
    }

    public function getCurrentYearBalanceAttribute()
    {
        $currentYear = date('Y');
        return $this->vacationBalances()
                    ->where('year', $currentYear)
                    ->first();
    }
}