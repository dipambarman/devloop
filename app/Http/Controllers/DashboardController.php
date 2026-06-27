<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $stats = $this->dashboardService->getStats($user);
        $recentProjects = $this->dashboardService->getRecentProjects($user);
        $recentTasks = $this->dashboardService->getRecentTasks($user);
        $taskDistribution = $this->dashboardService->getTaskDistribution($user);

        return view('dashboard', compact(
            'stats',
            'recentProjects',
            'recentTasks',
            'taskDistribution'
        ));
    }
}
