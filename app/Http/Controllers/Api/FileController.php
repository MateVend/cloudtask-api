<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function uploadTaskAttachment(Request $request, Task $task)
    {
        if ($task->organization_id !== $request->user()->current_organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('task-attachments', $filename, 'public');

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'uploaded_by' => $request->user()->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
        ]);

        return response()->json($attachment->load('uploader'), 201);
    }

    public function deleteTaskAttachment(Request $request, TaskAttachment $attachment)
    {
        if ($attachment->task->organization_id !== $request->user()->current_organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted successfully']);
    }

    public function downloadTaskAttachment(TaskAttachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return Storage::disk('public')->download($attachment->path, $attachment->original_filename);
    }

    public function uploadOrganizationLogo(Request $request)
    {
        $organization = Organization::find($request->user()->current_organization_id);

        if (!$request->user()->isAdmin($organization->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($organization->logo) {
            Storage::disk('public')->delete($organization->logo);
        }

        $path = $request->file('logo')->store('logos', 'public');

        $organization->update(['logo' => $path]);

        return response()->json([
            'logo' => Storage::url($path),
            'organization' => $organization,
        ]);
    }

    public function uploadUserAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return response()->json([
            'avatar' => Storage::url($path),
            'user' => $user,
        ]);
    }
}
