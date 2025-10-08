<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $organizations = $request->user()->organizations()->with('users')->get();
        return response()->json($organizations);
    }

    public function show(Request $request, Organization $organization)
    {
        if (!$request->user()->organizations()->where('organization_id', $organization->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $organization->load(['users', 'projects', 'activeSubscription']);

        $stats = [
            'total_projects' => $organization->projects()->count(),
            'active_projects' => $organization->projects()->where('status', 'active')->count(),
            'total_tasks' => $organization->tasks()->count(),
            'completed_tasks' => $organization->tasks()->where('status', 'completed')->count(),
            'total_users' => $organization->users()->count(),
        ];

        return response()->json([
            'organization' => $organization,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        if (!$request->user()->isAdmin($organization->id)) {
            return response()->json([
                'message' => 'You are not allowed to delete this organization. Only administrators can perform this action.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($organization->logo) {
                Storage::delete($organization->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $organization->update($validated);

        return response()->json($organization);
    }

    public function destroy(Request $request, Organization $organization)
    {
        if (!$request->user()->isAdmin($organization->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($organization->logo) {
            Storage::delete($organization->logo);
        }

        $organization->delete();

        return response()->json(['message' => 'Organization deleted successfully']);
    }

    public function updatePlan(Request $request, Organization $organization)
    {
        if (!$request->user()->isAdmin($organization->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'plan' => 'required|in:free,pro,enterprise',
        ]);

        $limits = [
            'free' => ['projects' => 3, 'users' => 5],
            'pro' => ['projects' => 50, 'users' => 20],
            'enterprise' => ['projects' => 999999, 'users' => 999999],
        ];

        $organization->update([
            'plan' => $validated['plan'],
            'project_limit' => $limits[$validated['plan']]['projects'],
            'user_limit' => $limits[$validated['plan']]['users'],
        ]);

        return response()->json($organization);
    }

    public function getUsage(Request $request, Organization $organization)
    {
        if (!$request->user()->organizations()->where('organization_id', $organization->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'projects' => [
                'used' => $organization->projects()->count(),
                'limit' => $organization->project_limit,
            ],
            'users' => [
                'used' => $organization->users()->count(),
                'limit' => $organization->user_limit,
            ],
            'storage' => [
                'used' => 0,
                'limit' => 1000,
            ],
        ]);
    }
}
