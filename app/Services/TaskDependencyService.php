<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskDependencyService
{
    /**
     * Add a prerequisite dependency: $task depends on $dependsOnTask.
     * Prevents self-dependency, cross-project dependencies, duplicates, and cycles.
     *
     * @throws ValidationException
     */
    public function addDependency(Task $task, Task $dependsOnTask): void
    {
        if ($task->id === $dependsOnTask->id) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => ['A task cannot depend on itself.'],
            ]);
        }

        if ($task->project_id !== $dependsOnTask->project_id) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => ['Prerequisite task must belong to the same project.'],
            ]);
        }

        if ($task->dependencies()->where('depends_on_task_id', $dependsOnTask->id)->exists()) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => ['This prerequisite dependency already exists.'],
            ]);
        }

        // Circular Dependency Check using BFS:
        // If $dependsOnTask can reach $task through its prerequisites, adding $task -> $dependsOnTask would form a cycle.
        if ($this->hasPath($dependsOnTask->id, $task->id)) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => ['Cannot add dependency: this would create a circular dependency loop.'],
            ]);
        }

        $task->dependencies()->attach($dependsOnTask->id);
    }

    /**
     * Check if a directed path exists from $fromTaskId to $toTaskId through existing dependencies.
     * Time complexity: O(V + E) BFS.
     */
    public function hasPath(int $fromTaskId, int $toTaskId): bool
    {
        $visited = [];
        $queue = [$fromTaskId];

        while (! empty($queue)) {
            $currentId = array_shift($queue);

            if ($currentId === $toTaskId) {
                return true;
            }

            if (isset($visited[$currentId])) {
                continue;
            }

            $visited[$currentId] = true;

            // Fetch adjacent dependencies of the current node
            $prerequisites = DB::table('task_dependencies')
                ->where('task_id', $currentId)
                ->pluck('depends_on_task_id')
                ->all();

            foreach ($prerequisites as $prereqId) {
                if (! isset($visited[$prereqId])) {
                    $queue[] = (int) $prereqId;
                }
            }
        }

        return false;
    }

    /**
     * Check if all prerequisites of a task are completed.
     */
    public function canMarkDone(Task $task): bool
    {
        return ! $task->isBlocked();
    }
}
