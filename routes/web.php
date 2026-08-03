<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SnippetController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

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

    Route::get('projects/{project}/board', [ProjectController::class, 'board'])->name('projects.board');

    Route::scopeBindings()->group(function () {
        Route::get('projects/{project}/discussions', [DiscussionController::class, 'index'])->name('projects.discussions.index');
        Route::get('projects/{project}/discussions/{discussion}', [DiscussionController::class, 'show'])->name('projects.discussions.show');
    });

    Route::resource('projects', ProjectController::class)->only(['index', 'show', 'create', 'edit']);
    Route::resource('tasks', TaskController::class)->only(['index', 'show', 'create', 'edit']);
    Route::resource('notes', NoteController::class)->only(['index', 'show', 'create', 'edit']);
    Route::resource('snippets', SnippetController::class)->only(['index', 'show', 'create', 'edit']);

    // ── WRITE Routes (30 req/min) ────────────────────────────────
    // State-changing operations: update, patch, post.
    Route::middleware('throttle:write')->group(function () {
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

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
        });
    });
});

require __DIR__.'/auth.php';
