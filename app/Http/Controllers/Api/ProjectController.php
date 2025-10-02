<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Organization;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $organizationId = $request->user()->current_organization_id;

        $projects = Project::where('organization_id', $organizationId)
            ->with(['creator', 'users', 'tasks'])
            ->withCount('tasks')
            ->get()
            ->map(function ($project) {
                $project->progress = $project->progress;
                return $project;
            });

        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $organization = Organization::find($user->current_organization_id);

        if (!$organization->canAddProject()) {
            return response()->json([
                'message' => 'Project limit reached. Please upgrade your plan.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,on_hold,completed,archived',
            'color' => 'sometimes|string|max:7',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project = Project::create([
            ...$validated,
            'organization_id' => $user->current_organization_id,
            'created_by' => $user->id,
        ]);

        $project->users()->attach($user->id);

        return response()->json($project->load(['creator', 'users']), 201);
    }

    public function show(Project $project)
    {
        $project->load(['creator', 'users', 'tasks.assignedUser', 'tasks.comments.user']);
        $project->progress = $project->progress;
        $project->task_counts = $project->getTaskCountsByStatus();

        return response()->json($project);
    }

    public function update(Request $request, Project $project)
    {
        if ($project->organization_id !== $request->user()->current_organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,on_hold,completed,archived',
            'color' => 'sometimes|string|max:7',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project->update($validated);

        return response()->json($project->load(['creator', 'users']));
    }

    public function destroy(Project $project)
    {
        if ($project->organization_id !== request()->user()->current_organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }

    public function addMember(Request $request, Project $project)
    {
        if ($project->organization_id !== $request->user()->current_organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if (!$project->organization->users()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json(['message' => 'User is not a member of this organization'], 400);
        }

        $project->users()->syncWithoutDetaching($validated['user_id']);

        return response()->json($project->load('users'));
    }

    public function removeMember(Request $request, Project $project, $userId)
    {
        if ($project->organization_id !== $request->user()->current_organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->users()->detach($userId);

        return response()->json($project->load('users'));
    }
}
