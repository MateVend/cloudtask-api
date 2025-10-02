<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\TeamInvitation;
use Illuminate\Support\Facades\Mail;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $organizationId = $request->user()->current_organization_id;
        $organization = Organization::find($organizationId);

        $members = $organization->users()
            ->withCount(['assignedTasks', 'createdTasks'])
            ->with('assignedTasks')
            ->get()
            ->map(function ($user) {
                $completedTasks = $user->assignedTasks->where('status', 'completed')->count();
                $totalTasks = $user->assignedTasks->count();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'role' => $user->pivot->role,
                    'last_active_at' => $user->last_active_at,
                    'assigned_tasks_count' => $totalTasks,
                    'created_tasks_count' => $user->created_tasks_count,
                    'completed_tasks_count' => $completedTasks,
                ];
            });

        return response()->json($members);
    }

    public function invite(Request $request)
    {
        $user = $request->user();
        $organization = Organization::find($user->current_organization_id);

        if (!$user->isManager($organization->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$organization->canAddUser()) {
            return response()->json([
                'message' => 'User limit reached. Please upgrade your plan.',
            ], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,manager,member',
        ]);

        $existingUser = User::where('email', $validated['email'])->first();

        if ($existingUser) {
            if ($organization->users()->where('user_id', $existingUser->id)->exists()) {
                return response()->json(['message' => 'User already in organization'], 400);
            }

            $organization->users()->attach($existingUser->id, ['role' => $validated['role']]);

            Mail::to($existingUser->email)->send(new TeamInvitation(
                $existingUser,
                $organization,
                $request->user()
            ));

            return response()->json([
                'message' => 'User added to organization',
                'user' => $existingUser,
            ]);
        }


        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(16)),
            'current_organization_id' => $organization->id,
        ]);

        $organization->users()->attach($newUser->id, ['role' => $validated['role']]);

        return response()->json([
            'message' => 'User invited successfully',
            'user' => $newUser,
        ], 201);
    }

    public function updateRole(Request $request, $userId)
    {
        $user = $request->user();
        $organization = Organization::find($user->current_organization_id);

        if (!$user->isAdmin($organization->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'role' => 'required|in:admin,manager,member',
        ]);

        $organization->users()->updateExistingPivot($userId, ['role' => $validated['role']]);

        return response()->json(['message' => 'Role updated successfully']);
    }

    public function remove(Request $request, $userId)
    {
        $user = $request->user();
        $organization = Organization::find($user->current_organization_id);

        if (!$user->isAdmin($organization->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($userId == $user->id) {
            return response()->json(['message' => 'Cannot remove yourself'], 400);
        }

        $organization->users()->detach($userId);

        return response()->json(['message' => 'Member removed successfully']);
    }
}
