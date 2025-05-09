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
    
    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

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

    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    public function totalTimeSpent()
    {
        return $this->timeLogs()->sum('minutes');
    }

    public function formattedTotalTime()
    {
        $total = $this->totalTimeSpent();
        $hours = floor($total / 60);
        $mins = $total % 60;
        
        $result = '';
        if ($hours > 0) {
            $result .= $hours . 'h ';
        }
        if ($mins > 0 || $hours == 0) {
            $result .= $mins . 'm';
        }
        
        return trim($result);
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class)->orderBy('order');
    }

    public function completedSubtasksCount()
    {
        return $this->subtasks()->where('is_completed', true)->count();
    }

    public function subtaskCompletionPercentage()
    {
        $total = $this->subtasks()->count();
        if ($total === 0) {
            return 0;
        }
        
        $completed = $this->completedSubtasksCount();
        return round(($completed / $total) * 100);
    }
}