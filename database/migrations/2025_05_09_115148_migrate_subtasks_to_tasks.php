<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First make sure we have parent_id column
        if (!Schema::hasColumn('tasks', 'parent_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('project_id');
                $table->foreign('parent_id')->references('id')->on('tasks')->onDelete('cascade');
            });
        }
        
        // Also add order if not exists
        if (!Schema::hasColumn('tasks', 'order')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->integer('order')->nullable()->after('story_points');
            });
        }
        
        // Only proceed if subtasks table exists
        if (Schema::hasTable('subtasks')) {
            // Get all subtasks
            $subtasks = DB::table('subtasks')->get();
            
            foreach ($subtasks as $subtask) {
                // Get the parent task to access its project_id
                $parentTask = DB::table('tasks')->where('id', $subtask->task_id)->first();
                
                if ($parentTask) {
                    // Get the latest task number for the project
                    $latestTaskNumber = DB::table('tasks')
                        ->where('project_id', $parentTask->project_id)
                        ->orderBy('id', 'desc')
                        ->value('task_number');
                    
                    // Parse the next task number
                    $taskNumber = '';
                    if ($latestTaskNumber) {
                        $parts = explode('-', $latestTaskNumber);
                        $nextNumber = (int)$parts[1] + 1;
                        $taskNumber = $parts[0] . '-' . $nextNumber;
                    } else {
                        // Fallback if no existing tasks
                        $project = DB::table('projects')->where('id', $parentTask->project_id)->first();
                        $taskNumber = $project ? $project->key . '-1' : 'UNKNOWN-1';
                    }
                    
                    // Determine status, type, and priority IDs
                    $taskStatusId = $parentTask->task_status_id;
                    $taskTypeId = $parentTask->task_type_id;
                    $priorityId = $parentTask->priority_id;
                    
                    // Convert completed status
                    $closedAt = $subtask->is_completed ? $subtask->completed_at : null;
                    
                    // Insert as a new task
                    DB::table('tasks')->insert([
                        'title' => $subtask->title,
                        'description' => $subtask->description,
                        'task_number' => $taskNumber,
                        'project_id' => $parentTask->project_id,
                        'parent_id' => $subtask->task_id,
                        'reporter_id' => $parentTask->reporter_id,
                        'assignee_id' => $subtask->assignee_id,
                        'task_status_id' => $taskStatusId,
                        'task_type_id' => $taskTypeId,
                        'priority_id' => $priorityId,
                        'order' => $subtask->order,
                        'closed_at' => $closedAt,
                        'created_at' => $subtask->created_at,
                        'updated_at' => $subtask->updated_at,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This is a data migration, there's no reliable way to reverse it
        // But we can delete all tasks that have a parent_id
        DB::table('tasks')->whereNotNull('parent_id')->delete();
    }
};