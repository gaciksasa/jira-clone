<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SprintController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::redirect('/home', '/dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Projects
    Route::resource('projects', ProjectController::class);
    
    // Project Board
    Route::get('/projects/{project}/board', [ProjectController::class, 'board'])->name('projects.board');
    
    // Project Members
    Route::get('/projects/{project}/members', [ProjectController::class, 'members'])->name('projects.members');
    Route::put('/projects/{project}/members', [ProjectController::class, 'updateMembers'])->name('projects.members.update');
    
    // Tasks
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index'])->name('projects.tasks.index');
    Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])->name('projects.tasks.create');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::get('/projects/{project}/tasks/{task}', [TaskController::class, 'show'])->name('projects.tasks.show');
    Route::get('/projects/{project}/tasks/{task}/edit', [TaskController::class, 'edit'])->name('projects.tasks.edit');
    Route::put('/projects/{project}/tasks/{task}', [TaskController::class, 'update'])->name('projects.tasks.update');
    Route::delete('/projects/{project}/tasks/{task}', [TaskController::class, 'destroy'])->name('projects.tasks.destroy');
    Route::patch('/projects/{project}/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('projects.tasks.updateStatus');
    Route::post('/projects/{project}/tasks/{task}/comments', [TaskController::class, 'addComment'])->name('projects.tasks.comments.store');
    
    // Sprints
    Route::get('/projects/{project}/sprints', [SprintController::class, 'index'])->name('projects.sprints.index');
    Route::get('/projects/{project}/sprints/create', [SprintController::class, 'create'])->name('projects.sprints.create');
    Route::post('/projects/{project}/sprints', [SprintController::class, 'store'])->name('projects.sprints.store');
    Route::get('/projects/{project}/sprints/{sprint}', [SprintController::class, 'show'])->name('projects.sprints.show');
    Route::get('/projects/{project}/sprints/{sprint}/edit', [SprintController::class, 'edit'])->name('projects.sprints.edit');
    Route::put('/projects/{project}/sprints/{sprint}', [SprintController::class, 'update'])->name('projects.sprints.update');
    Route::delete('/projects/{project}/sprints/{sprint}', [SprintController::class, 'destroy'])->name('projects.sprints.destroy');
    Route::post('/projects/{project}/sprints/{sprint}/start', [SprintController::class, 'start'])->name('projects.sprints.start');
    Route::post('/projects/{project}/sprints/{sprint}/complete', [SprintController::class, 'complete'])->name('projects.sprints.complete');
    Route::get('/projects/{project}/sprints/{sprint}/backlog', [SprintController::class, 'backlog'])->name('projects.sprints.backlog');
    Route::post('/projects/{project}/sprints/{sprint}/tasks', [SprintController::class, 'addTasks'])->name('projects.sprints.tasks.add');
    Route::delete('/projects/{project}/sprints/{sprint}/tasks', [SprintController::class, 'removeTasks'])->name('projects.sprints.tasks.remove');
});