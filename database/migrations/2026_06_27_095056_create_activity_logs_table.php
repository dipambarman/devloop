<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event'); // e.g. task_created, status_changed, comment_added
            $table->nullableMorphs('subject'); // Polymorphic: Task, Comment, Discussion, etc.
            $table->string('description'); // Human-readable: "created task 'Fix login bug'"
            $table->json('meta')->nullable(); // Structured data: {old_status: 'todo', new_status: 'done'}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
