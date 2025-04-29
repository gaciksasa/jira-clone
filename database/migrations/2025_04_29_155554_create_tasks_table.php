<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('task_number'); // e.g., PROJ-123
            $table->foreignId('project_id')->constrained();
            $table->foreignId('reporter_id')->constrained('users');
            $table->foreignId('assignee_id')->nullable()->constrained('users');
            $table->foreignId('task_status_id')->constrained();
            $table->foreignId('task_type_id')->constrained();
            $table->foreignId('priority_id')->constrained();
            $table->foreignId('sprint_id')->nullable()->constrained();
            $table->integer('story_points')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};