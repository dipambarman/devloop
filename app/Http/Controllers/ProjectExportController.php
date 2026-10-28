<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectExportController extends Controller
{
    /**
     * Export project tasks to CSV or JSON format.
     */
    public function exportTasks(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $format = strtolower($request->query('format', 'csv'));

        if ($format === 'json') {
            $tasks = $project->tasks()->with(['assignee', 'tags'])->get();
            $filename = "{$project->slug}-tasks-".now()->format('Y-m-d').'.json';

            return response()->json($tasks, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // Default: CSV Stream
        $filename = "{$project->slug}-tasks-".now()->format('Y-m-d').'.csv';

        return new StreamedResponse(function () use ($project) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'Task ID',
                'Title',
                'Description',
                'Status',
                'Priority',
                'Assignee Name',
                'Assignee Email',
                'Due Date',
                'Estimated Hours',
                'Created At',
            ]);

            $project->tasks()->with('assignee')->chunk(100, function ($tasks) use ($handle) {
                foreach ($tasks as $task) {
                    fputcsv($handle, [
                        $task->id,
                        $task->title,
                        $task->description,
                        is_object($task->status) ? $task->status->value : $task->status,
                        is_object($task->priority) ? $task->priority->value : $task->priority,
                        $task->assignee?->name ?? 'Unassigned',
                        $task->assignee?->email ?? '',
                        $task->due_date ? $task->due_date->format('Y-m-d') : '',
                        $task->estimated_hours ?? '',
                        $task->created_at->toIso8601String(),
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export project bugs / errors to CSV or JSON format.
     */
    public function exportErrors(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $format = strtolower($request->query('format', 'csv'));

        if ($format === 'json') {
            $bugs = $project->bugs()->with('assignee')->get();
            $filename = "{$project->slug}-errors-".now()->format('Y-m-d').'.json';

            return response()->json($bugs, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // Default: CSV Stream
        $filename = "{$project->slug}-errors-".now()->format('Y-m-d').'.csv';

        return new StreamedResponse(function () use ($project) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Bug ID',
                'Fingerprint',
                'Title',
                'Error Type',
                'Severity',
                'Status',
                'Occurrences',
                'Regressions',
                'Assignee',
                'Last Seen At',
                'Created At',
            ]);

            $project->bugs()->with('assignee')->chunk(100, function ($bugs) use ($handle) {
                foreach ($bugs as $bug) {
                    fputcsv($handle, [
                        $bug->id,
                        $bug->fingerprint,
                        $bug->title,
                        $bug->error_type,
                        $bug->severity,
                        $bug->status,
                        $bug->occurrence_count,
                        $bug->regressions,
                        $bug->assignee?->name ?? 'Unassigned',
                        $bug->last_seen_at ? $bug->last_seen_at->toIso8601String() : '',
                        $bug->created_at->toIso8601String(),
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
