<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'description',
        'lead_id',
        'department_id',
    ];

    public function lead()
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function sprints()
    {
        return $this->hasMany(Sprint::class);
    }

    public function taskStatuses()
    {
        return $this->hasMany(TaskStatus::class);
    }

    public function labels()
    {
        return $this->hasMany(Label::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Calculate total time spent across all tasks in the project
    public function totalTimeSpent()
    {
        return $this->hasManyThrough(TimeLog::class, Task::class)->sum('minutes');
    }

    // Helper function to format time
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
}