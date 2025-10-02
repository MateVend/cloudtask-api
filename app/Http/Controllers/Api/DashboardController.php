<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $organizationId = $request->user()->current_organization_id;
        $organization = Organization::find($organizationId);

        $stats = [
            'total_projects' => $organization->projects()->count(),
            'active_projects' => $organization->projects()->where('status', 'active')->count(),
            'total_tasks' => $organization->tasks()->count(),
            'completed_tasks' => $organization->tasks()->where('status', 'completed')->count(),
            'in_progress_tasks' => $organization->tasks()->where('status', 'in_progress')->count(),
            'overdue_tasks' => $organization->tasks()
                ->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count(),
            'total_members' => $organization->users()->count(),
        ];

        $tasksByStatus = $organization->tasks()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $tasksByPriority = $organization->tasks()
            ->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority');

        $recentProjects = $organization->projects()
            ->with('creator')
            ->latest()
            ->take(5)
            ->get();

        $recentTasks = $organization->tasks()
            ->with(['project', 'assignedUser'])
            ->latest()
            ->take(10)
            ->get();

        $projectProgress = $organization->projects()
            ->where('status', 'active')
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'progress' => $project->progress,
                    'total_tasks' => $project->tasks()->count(),
                    'completed_tasks' => $project->tasks()->where('status', 'completed')->count(),
                ];
            });

        $myTasks = $organization->tasks()
            ->where('assigned_to', $request->user()->id)
            ->where('status', '!=', 'completed')
            ->with('project')
            ->orderBy('due_date')
            ->take(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'tasks_by_status' => $tasksByStatus,
            'tasks_by_priority' => $tasksByPriority,
            'recent_projects' => $recentProjects,
            'recent_tasks' => $recentTasks,
            'project_progress' => $projectProgress,
            'my_tasks' => $myTasks,
        ]);
    }
}
