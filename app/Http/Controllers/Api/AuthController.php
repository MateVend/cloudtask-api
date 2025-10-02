<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'organization_name' => 'required|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $organization = Organization::create([
            'name' => $validated['organization_name'],
            'plan' => 'free',
            'project_limit' => 3,
            'user_limit' => 5,
        ]);

        $organization->users()->attach($user->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        Mail::to($user->email)->send(new WelcomeEmail($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('currentOrganization'),
            'organization' => $organization,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->update(['last_active_at' => now()]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load(['currentOrganization', 'organizations']),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['currentOrganization', 'organizations.users']),
        ]);
    }

    public function switchOrganization(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $user = $request->user();

        if (!$user->organizations()->where('organization_id', $validated['organization_id'])->exists()) {
            return response()->json(['message' => 'You are not a member of this organization'], 403);
        }

        $user->update(['current_organization_id' => $validated['organization_id']]);

        return response()->json([
            'user' => $user->load('currentOrganization'),
        ]);
    }
}
