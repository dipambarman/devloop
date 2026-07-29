<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('projects/{project}/board', [\App\Http\Controllers\ProjectController::class, 'board'])->name('projects.board');
    Route::post('projects/{project}/members', [\App\Http\Controllers\ProjectController::class, 'addMember'])->name('projects.addMember');
    Route::delete('projects/{project}/members/{user}', [\App\Http\Controllers\ProjectController::class, 'removeMember'])->name('projects.removeMember');
    
    // Project Discussions (scoped binding ensures discussion belongs to the project)
    Route::scopeBindings()->group(function () {
        Route::get('projects/{project}/discussions', [\App\Http\Controllers\DiscussionController::class, 'index'])->name('projects.discussions.index');
        Route::post('projects/{project}/discussions', [\App\Http\Controllers\DiscussionController::class, 'store'])->name('projects.discussions.store');
        Route::get('projects/{project}/discussions/{discussion}', [\App\Http\Controllers\DiscussionController::class, 'show'])->name('projects.discussions.show');
        Route::delete('projects/{project}/discussions/{discussion}', [\App\Http\Controllers\DiscussionController::class, 'destroy'])->name('projects.discussions.destroy');
        Route::patch('projects/{project}/discussions/{discussion}/pin', [\App\Http\Controllers\DiscussionController::class, 'togglePin'])->name('projects.discussions.togglePin');
        Route::post('projects/{project}/discussions/{discussion}/replies', [\App\Http\Controllers\DiscussionController::class, 'storeReply'])->name('projects.discussions.replies.store');
        Route::delete('projects/{project}/discussions/{discussion}/replies/{reply}', [\App\Http\Controllers\DiscussionController::class, 'destroyReply'])->name('projects.discussions.replies.destroy');
    });

    Route::resource('projects', \App\Http\Controllers\ProjectController::class);
    
    Route::patch('tasks/{task}/status', [\App\Http\Controllers\TaskController::class, 'updateStatusInline'])->name('tasks.updateStatusInline');
    Route::patch('tasks/{task}/priority', [\App\Http\Controllers\TaskController::class, 'updatePriorityInline'])->name('tasks.updatePriorityInline');
    Route::patch('tasks/{task}/assignee', [\App\Http\Controllers\TaskController::class, 'updateAssigneeInline'])->name('tasks.updateAssigneeInline');
    Route::post('tasks/{task}/status-board', [\App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::resource('tasks', \App\Http\Controllers\TaskController::class);

    Route::post('tasks/{task}/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [\App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');

    Route::patch('notes/{note}/toggle-pin', [\App\Http\Controllers\NoteController::class, 'togglePin'])->name('notes.togglePin');
    Route::resource('notes', \App\Http\Controllers\NoteController::class);
    
    Route::resource('snippets', \App\Http\Controllers\SnippetController::class);

    Route::patch('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
});

require __DIR__.'/auth.php';
