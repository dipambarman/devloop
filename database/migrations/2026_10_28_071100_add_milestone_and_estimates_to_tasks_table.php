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
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('milestone_id')->nullable()->after('sprint_id')->constrained('milestones')->nullOnDelete();
            $table->decimal('estimated_hours', 6, 2)->nullable()->after('due_date');

            $table->index(['project_id', 'milestone_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['milestone_id']);
            $table->dropIndex(['project_id', 'milestone_id']);
            $table->dropColumn(['milestone_id', 'estimated_hours']);
        });
    }
};
