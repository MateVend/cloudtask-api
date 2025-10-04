<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        $organizationId = $request->user()->current_organization_id;

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Search Projects
        $projects = Project::where('organization_id', $organizationId)
            ->where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name', 'color']);

        foreach ($projects as $project) {
            $results[] = [
                'type' => 'project',
                'name' => $project->name,
                'path' => '/projects/' . $project->id,
                'meta' => ['color' => $project->color]
            ];
        }

        // Search Tasks
        $tasks = Task::where('organization_id', $organizationId)
            ->where('title', 'like', "%{$query}%")
            ->with('project:id,name')
            ->limit(5)
            ->get(['id', 'title', 'project_id', 'status']);

        foreach ($tasks as $task) {
            $results[] = [
                'type' => 'task',
                'name' => $task->title,
                'path' => '/tasks',
                'meta' => [
                    'project' => $task->project->name ?? 'Unknown',
                    'status' => $task->status
                ]
            ];
        }

        // Search Team Members
        $organization = $request->user()->currentOrganization;
        $members = $organization->users()
            ->where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name', 'email']);

        foreach ($members as $member) {
            $results[] = [
                'type' => 'team',
                'name' => $member->name,
                'path' => '/team',
                'meta' => ['email' => $member->email]
            ];
        }

        return response()->json($results);
    }
}
