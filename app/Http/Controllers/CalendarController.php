<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Display the task calendar.
     */
    public function index(Request $request, ?Project $project = null)
    {
        if ($project) {
            \Illuminate\Support\Facades\Gate::authorize('view', $project);
        }

        // Determine current viewing month and year
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        // Sanitize bounds
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }

        $currentDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        // Calculate grid start (Monday of the first week) and end (Sunday of the last week)
        $startOfCalendar = $currentDate->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $currentDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        // Fetch accessible projects
        $accessibleProjects = auth()->user()->allProjects()->get();
        $accessibleProjectIds = $accessibleProjects->pluck('id');

        // Query tasks with due dates in this calendar window
        $taskQuery = Task::whereIn('project_id', $project ? [$project->id] : $accessibleProjectIds)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [
                $startOfCalendar->format('Y-m-d'),
                $endOfCalendar->format('Y-m-d'),
            ])
            ->with(['project', 'assignee']);

        if ($request->filled('project_id') && !$project) {
            $taskQuery->where('project_id', $request->project_id);
        }

        if ($request->filled('priority')) {
            $taskQuery->where('priority', $request->priority);
        }

        if ($request->filled('status')) {
            $taskQuery->where('status', $request->status);
        }

        $tasks = $taskQuery->get();

        // Group tasks by date string (Y-m-d)
        $tasksByDate = [];
        foreach ($tasks as $task) {
            $dateKey = $task->due_date->format('Y-m-d');
            $tasksByDate[$dateKey][] = $task;
        }

        // Build days matrix for the view
        $calendarGrid = [];
        $cursor = $startOfCalendar->copy();

        while ($cursor->lte($endOfCalendar)) {
            $dateString = $cursor->format('Y-m-d');
            $calendarGrid[] = [
                'date' => $cursor->copy(),
                'date_string' => $dateString,
                'day_number' => $cursor->day,
                'is_current_month' => $cursor->month === $currentDate->month,
                'is_today' => $cursor->isToday(),
                'tasks' => $tasksByDate[$dateString] ?? [],
            ];
            $cursor->addDay();
        }

        return view('calendar.index', [
            'currentDate' => $currentDate,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'calendarGrid' => $calendarGrid,
            'projects' => $accessibleProjects,
            'selectedProject' => $project,
            'priorities' => TaskPriority::cases(),
            'statuses' => TaskStatus::cases(),
        ]);
    }
}
