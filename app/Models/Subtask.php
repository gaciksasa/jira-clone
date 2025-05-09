<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'title',
        'description',
        'assignee_id',
        'is_completed',
        'order',
        'completed_at'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * The parent task this subtask belongs to.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * The user assigned to this subtask.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Mark the subtask as completed.
     */
    public function complete()
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now()
        ]);
    }

    /**
     * Mark the subtask as not completed.
     */
    public function incomplete()
    {
        $this->update([
            'is_completed' => false,
            'completed_at' => null
        ]);
    }
}