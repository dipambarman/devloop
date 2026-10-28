<?php

use App\Http\Controllers\BugController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\ProjectKeyController;
use App\Http\Controllers\SnippetController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\SlackWebhookController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskDependencyController;
use App\Http\Controllers\TimeLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/changelog', 'changelog')->name('changelog');

// System Health & Monitoring Probe
Route::get('/health', \App\Http\Controllers\HealthCheckController::class)->name('health');
Route::get('/api/health', \App\Http\Controllers\HealthCheckController::class)->name('api.health');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ── Authenticated Routes ─────────────────────────────────────────────
// All routes below require authentication and are subject to the
// global rate limiter (60 req/min per user).
Route::middleware(['auth', 'throttle:global'])->group(function () {

    // ── READ Routes ──────────────────────────────────────────────
    // Index, show, edit forms, create forms — no extra throttle.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('projects/{project}/board', [ProjectController::class, 'board'])->name('projects.board');
    Route::get('projects/{project}/calendar', [CalendarController::class, 'index'])->name('projects.calendar');

    Route::scopeBindings()->group(function () {
        Route::get('projects/{project}/discussions', [DiscussionController::class, 'index'])->name('projects.discussions.index');
        Route::get('projects/{project}/discussions/{discussion}', [DiscussionController::class, 'show'])->name('projects.discussions.show');
        Route::get('projects/{project}/bugs', [BugController::class, 'index'])->name('projects.bugs.index');
        Route::get('projects/{project}/bugs/{bug}', [BugController::class, 'show'])->name('projects.bugs.show');
        Route::get('projects/{project}/sprints', [SprintController::class, 'index'])->name('projects.sprints.index');
        Route::get('projects/{project}/milestones', [MilestoneController::class, 'index'])->name('projects.milestones.index');
        Route::get('projects/{project}/time-report', [TimeLogController::class, 'report'])->name('projects.time-report');
    });

    Route::resource('projects', ProjectController::class)->only(['index', 'show', 'create', 'edit']);
    Route::resource('tasks', TaskController::class)->only(['index', 'show', 'create', 'edit']);
    Route::resource('notes', NoteController::class)->only(['index', 'show', 'create', 'edit']);
    Route::resource('snippets', SnippetController::class)->only(['index', 'show', 'create', 'edit']);

    // ── WRITE Routes (30 req/min) ────────────────────────────────
    // State-changing operations: update, patch, post.
    Route::middleware('throttle:write')->group(function () {
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/notifications', [ProfileController::class, 'updateNotifications'])->name('profile.notifications.update');

        Route::resource('projects', ProjectController::class)->only(['update']);
        Route::post('projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.addMember');
        Route::delete('projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('projects.removeMember');

        Route::resource('tasks', TaskController::class)->only(['update']);
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatusInline'])->name('tasks.updateStatusInline');
        Route::patch('tasks/{task}/priority', [TaskController::class, 'updatePriorityInline'])->name('tasks.updatePriorityInline');
        Route::patch('tasks/{task}/assignee', [TaskController::class, 'updateAssigneeInline'])->name('tasks.updateAssigneeInline');
        Route::post('tasks/{task}/status-board', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');

        Route::post('tasks/{task}/comments', [CommentController::class, 'store'])->name('comments.store');

        Route::resource('notes', NoteController::class)->only(['update']);
        Route::patch('notes/{note}/toggle-pin', [NoteController::class, 'togglePin'])->name('notes.togglePin');

        Route::resource('snippets', SnippetController::class)->only(['update']);

        Route::scopeBindings()->group(function () {
            Route::patch('projects/{project}/discussions/{discussion}/pin', [DiscussionController::class, 'togglePin'])->name('projects.discussions.togglePin');
            Route::post('projects/{project}/discussions/{discussion}/replies', [DiscussionController::class, 'storeReply'])->name('projects.discussions.replies.store');
            Route::post('projects/{project}/bugs/ingest', [BugController::class, 'store'])->name('projects.bugs.store');
            Route::patch('projects/{project}/bugs/{bug}/status', [BugController::class, 'updateStatus'])->name('projects.bugs.updateStatus');
            Route::post('projects/{project}/bugs/{bug}/convert-to-task', [BugController::class, 'convertToTask'])->name('projects.bugs.convertToTask');
            Route::post('projects/{project}/keys', [ProjectKeyController::class, 'store'])->name('projects.keys.store');
            Route::delete('projects/{project}/keys', [ProjectKeyController::class, 'destroy'])->name('projects.keys.destroy');
            Route::post('projects/{project}/slack-webhooks', [SlackWebhookController::class, 'store'])->name('projects.slackWebhooks.store');
            Route::patch('projects/{project}/slack-webhooks/{slackWebhook}', [SlackWebhookController::class, 'update'])->name('projects.slackWebhooks.update');
            Route::post('projects/{project}/slack-webhooks/{slackWebhook}/test', [SlackWebhookController::class, 'test'])->name('projects.slackWebhooks.test');
            Route::post('projects/{project}/sprints', [SprintController::class, 'store'])->name('projects.sprints.store');
            Route::patch('projects/{project}/sprints/{sprint}', [SprintController::class, 'update'])->name('projects.sprints.update');
            Route::patch('projects/{project}/sprints/{sprint}/start', [SprintController::class, 'start'])->name('projects.sprints.start');
            Route::patch('projects/{project}/sprints/{sprint}/complete', [SprintController::class, 'complete'])->name('projects.sprints.complete');
            Route::patch('projects/{project}/tasks/{task}/sprint', [SprintController::class, 'assignTask'])->name('projects.tasks.assignSprint');
            Route::post('projects/{project}/milestones', [MilestoneController::class, 'store'])->name('projects.milestones.store');
            Route::patch('projects/{project}/milestones/{milestone}', [MilestoneController::class, 'update'])->name('projects.milestones.update');
            Route::patch('projects/{project}/milestones/{milestone}/toggle', [MilestoneController::class, 'toggleStatus'])->name('projects.milestones.toggleStatus');
            Route::patch('projects/{project}/tasks/{task}/milestone', [MilestoneController::class, 'assignTask'])->name('projects.tasks.assignMilestone');
            Route::post('projects/{project}/tasks/{task}/dependencies', [TaskDependencyController::class, 'store'])->name('projects.tasks.dependencies.store');
            Route::post('projects/{project}/tasks/{task}/time-logs', [TimeLogController::class, 'store'])->name('projects.tasks.timeLogs.store');
        });

        Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    });

    // ── UPLOAD / CREATE Routes (10 req/min) ──────────────────────
    // Heavy creation operations.
    Route::middleware('throttle:upload')->group(function () {
        Route::resource('projects', ProjectController::class)->only(['store']);
        Route::resource('tasks', TaskController::class)->only(['store']);
        Route::resource('notes', NoteController::class)->only(['store']);
        Route::resource('snippets', SnippetController::class)->only(['store']);

        Route::scopeBindings()->group(function () {
            Route::post('projects/{project}/discussions', [DiscussionController::class, 'store'])->name('projects.discussions.store');
        });
    });

    // ── CRITICAL / DELETE Routes (5 req/min) ─────────────────────
    // Destructive operations — tightly limited.
    Route::middleware('throttle:critical')->group(function () {
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::resource('projects', ProjectController::class)->only(['destroy']);
        Route::resource('tasks', TaskController::class)->only(['destroy']);
        Route::resource('notes', NoteController::class)->only(['destroy']);
        Route::resource('snippets', SnippetController::class)->only(['destroy']);

        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

        Route::scopeBindings()->group(function () {
            Route::delete('projects/{project}/discussions/{discussion}', [DiscussionController::class, 'destroy'])->name('projects.discussions.destroy');
            Route::delete('projects/{project}/discussions/{discussion}/replies/{reply}', [DiscussionController::class, 'destroyReply'])->name('projects.discussions.replies.destroy');
            Route::delete('projects/{project}/bugs/{bug}', [BugController::class, 'destroy'])->name('projects.bugs.destroy');
            Route::delete('projects/{project}/sprints/{sprint}', [SprintController::class, 'destroy'])->name('projects.sprints.destroy');
            Route::delete('projects/{project}/milestones/{milestone}', [MilestoneController::class, 'destroy'])->name('projects.milestones.destroy');
            Route::delete('projects/{project}/tasks/{task}/dependencies/{dependency}', [TaskDependencyController::class, 'destroy'])->name('projects.tasks.dependencies.destroy');
            Route::delete('projects/{project}/tasks/{task}/time-logs/{timeLog}', [TimeLogController::class, 'destroy'])->name('projects.tasks.timeLogs.destroy');
            Route::delete('projects/{project}/slack-webhooks/{slackWebhook}', [SlackWebhookController::class, 'destroy'])->name('projects.slackWebhooks.destroy');
        });
    });
});

require __DIR__.'/auth.php';
