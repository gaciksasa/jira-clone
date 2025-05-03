<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'task_number',
        'project_id',
        'reporter_id',
        'assignee_id',
        'task_status_id',
        'task_type_id',
        'priority_id',
        'sprint_id',
        'story_points',
        'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];
    
    /**
     * Check if the task is closed.
     */
    public function isClosed()
    {
        return $this->closed_at !== null;
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function type()
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class);
    }
}