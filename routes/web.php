<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\AssignedTaskController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\TimeReportController;
use App\Http\Controllers\VacationController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\VacationSettingsController;


Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes();

// Language switcher
Route::post('/language', [App\Http\Controllers\LanguageController::class, 'changeLanguage'])->name('language.change');

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
    
    // Add this: Project Management
    Route::resource('projects', App\Http\Controllers\Admin\ProjectController::class);
    
    // User Management
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    
    // Role Management
    Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
    
    // Department Management
    Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);
    
    // Toggle user active status
    Route::patch('/users/{user}/toggle-active', [App\Http\Controllers\Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');

    // User Activities
    Route::get('/activities', [App\Http\Controllers\Admin\UserActivityController::class, 'index'])->name('activities.index');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\TimeReportController::class, 'index'])->name('index');
    });

    // Holidays management
    Route::resource('holidays', HolidayController::class);
    
    // Vacation settings
    Route::get('/vacation-settings', [VacationSettingsController::class, 'index'])->name('vacation-settings.index');
    Route::post('/vacation-settings', [VacationSettingsController::class, 'updateSettings'])->name('vacation-settings.update');
    Route::post('/vacation-requests/{vacationRequest}/approve', [VacationSettingsController::class, 'approve'])->name('vacation-requests.approve');
    Route::post('/vacation-requests/{vacationRequest}/reject', [VacationSettingsController::class, 'reject'])->name('vacation-requests.reject');
    Route::get('/vacation-report', [VacationSettingsController::class, 'report'])->name('vacation-report');
    Route::post('/vacation-recalculate', [VacationSettingsController::class, 'recalculateBalances'])->name('vacation-recalculate');
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
    Route::patch('/projects/{project}/tasks/{task}/close', [TaskController::class, 'close'])->name('projects.tasks.close');
    Route::patch('/projects/{project}/tasks/{task}/reopen', [TaskController::class, 'reopen'])->name('projects.tasks.reopen');

    // Subtask routes - now using the task controller for everything
    Route::prefix('projects/{project}/tasks/{task}/subtasks')->name('projects.tasks.subtasks.')->group(function () {
        Route::get('/create', [TaskController::class, 'createSubtask'])->name('create');
        Route::post('/', [TaskController::class, 'storeSubtask'])->name('store');
        Route::post('/reorder', [TaskController::class, 'reorderSubtasks'])->name('reorder');

        Route::get('/{subtask}/edit', [TaskController::class, 'editSubtask'])->name('edit');
        Route::put('/{subtask}', [TaskController::class, 'updateSubtask'])->name('update');
        Route::delete('/{subtask}', [TaskController::class, 'destroySubtask'])->name('destroy');
    });

    Route::patch('/projects/{project}/tasks/{task}/detach', [TaskController::class, 'detachFromParent'])->name('projects.tasks.detach');

    // Project Board Management
    Route::prefix('projects/{project}/statuses')->name('projects.statuses.')->group(function () {
        Route::get('/', [TaskStatusController::class, 'index'])->name('index');
        Route::get('/create', [TaskStatusController::class, 'create'])->name('create');
        Route::post('/', [TaskStatusController::class, 'store'])->name('store');
        Route::get('/{taskStatus}/edit', [TaskStatusController::class, 'edit'])->name('edit');
        Route::put('/{taskStatus}', [TaskStatusController::class, 'update'])->name('update');
        Route::delete('/{taskStatus}', [TaskStatusController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [TaskStatusController::class, 'reorder'])->name('reorder');
    });

    // Project Labels Management
    Route::prefix('projects/{project}/labels')->name('projects.labels.')->group(function () {
        Route::get('/', [LabelController::class, 'index'])->name('index');
        Route::get('/create', [LabelController::class, 'create'])->name('create');
        Route::post('/', [LabelController::class, 'store'])->name('store');
        Route::get('/{label}/edit', [LabelController::class, 'edit'])->name('edit');
        Route::put('/{label}', [LabelController::class, 'update'])->name('update');
        Route::delete('/{label}', [LabelController::class, 'destroy'])->name('destroy');
    });

    // Tasks by label
    Route::get('/projects/{project}/tasks/label/{label}', [TaskController::class, 'indexByLabel'])
    ->name('projects.tasks.by-label');
    
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

    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [UserProfileController::class, 'editPassword'])->name('profile.password');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Department user management
    Route::post('/departments/{department}/users', [App\Http\Controllers\Admin\DepartmentController::class, 'addUser'])->name('admin.departments.addUser');
    Route::delete('/departments/{department}/users/{user}', [App\Http\Controllers\Admin\DepartmentController::class, 'removeUser'])->name('admin.departments.removeUser');

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

    // Task Attachments
    Route::post('/projects/{project}/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])->name('projects.tasks.attachments.store');
    Route::get('/projects/{project}/tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('projects.tasks.attachments.download');
    Route::delete('/projects/{project}/tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])->name('projects.tasks.attachments.destroy');

    // Project Invitation
    Route::get('/invitation/{token}', [ProjectMemberController::class, 'acceptInvitation'])->name('invitation.accept');

    // Profile Avatar
    Route::get('/profile/avatar', [App\Http\Controllers\UserProfileController::class, 'editAvatar'])->name('profile.avatar');
    Route::put('/profile/avatar', [App\Http\Controllers\UserProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

    // Time Logging
    Route::post('/projects/{project}/tasks/{task}/time-logs', [TimeLogController::class, 'store'])->name('projects.tasks.time-logs.store');
    Route::delete('/projects/{project}/tasks/{task}/time-logs/{timeLog}', [TimeLogController::class, 'destroy'])->name('projects.tasks.time-logs.destroy');

    // Timesheet Routes
    Route::get('/timesheet', [TimesheetController::class, 'index'])->name('timesheet.index');
    Route::post('/timesheet/update', [TimesheetController::class, 'updateTime'])->name('timesheet.update');

    // API Routes for tasks
    Route::prefix('api')->group(function () {
        Route::get('/projects/{project}/tasks', [App\Http\Controllers\Api\TaskController::class, 'index']);
    });

    // Vacation routes for users
    Route::middleware(['auth'])->group(function () {
        Route::get('/vacation', [VacationController::class, 'index'])->name('vacation.index');
        Route::post('/vacation', [VacationController::class, 'store'])->name('vacation.store');
        Route::get('/vacation/{vacationRequest}', [VacationController::class, 'show'])->name('vacation.show');
        Route::post('/vacation/{vacationRequest}/cancel', [VacationController::class, 'cancel'])->name('vacation.cancel');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/unread', [App\Http\Controllers\NotificationController::class, 'getUnreadNotifications'])->name('unread');
    });
});