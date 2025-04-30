<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\ProjectMemberController;

Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes();

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
    
    // User Management
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    
    // Role Management
    Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
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

    // Project Members Management
    Route::prefix('projects/{project}/members')->name('projects.members.')->group(function () {
        Route::get('/', [ProjectMemberController::class, 'index'])->name('index');
        Route::put('/', [ProjectMemberController::class, 'update'])->name('update');
        Route::post('/invite', [ProjectMemberController::class, 'invite'])->name('invite');
        Route::get('/edit-lead', [ProjectMemberController::class, 'editLead'])->name('edit-lead');
        Route::put('/update-lead', [ProjectMemberController::class, 'updateLead'])->name('update-lead');
        Route::delete('/{user}', [ProjectMemberController::class, 'removeMember'])->name('remove');
        Route::get('/{member}', [ProjectMemberController::class, 'show'])->name('show');
    });

    // Project Invitation
    Route::get('/invitation/{token}', [ProjectMemberController::class, 'acceptInvitation'])->name('invitation.accept');
});