<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bugs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('fingerprint', 16);
            $table->string('title', 255);
            $table->string('error_type', 100)->default('UnknownError');
            $table->string('status', 30)->default('open');
            $table->string('severity', 30)->default('medium');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('occurrence_count')->default(1);
            $table->unsignedInteger('regressions')->default(0);
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->text('raw_message')->nullable();
            $table->mediumText('raw_stack')->nullable();
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'fingerprint']);
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'severity']);
            $table->index(['project_id', 'last_seen_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bugs');
    }
};
