<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Mail\TaskAssigned;
use App\Mail\TaskCommentNotification;
use Illuminate\Support\Facades\Mail;
use App\Events\TaskAssignedEvent;
use App\Events\TaskCompletedEvent;
use App\Events\CommentAddedEvent;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $organizationId = $request->user()->current_organization_id;

        $query = Task::where('organization_id', $organizationId)
            ->with(['project', 'assignedUser', 'creator']);

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tasks = $query->orderBy('order')->orderBy('created_at', 'desc')->get();

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:todo,in_progress,review,completed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $task = Task::create([
            ...$validated,
            'organization_id' => $request->user()->current_organization_id,
            'created_by' => $request->user()->id,
        ]);

        if ($task->assigned_to) {
            event(new TaskAssignedEvent($task, $request->user()));
        }

        if ($task->assigned_to) {
            Mail::to($task->assignedUser->email)->send(new TaskAssigned($task, $request->user()));
        }

        if ($task->assigned_to) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'organization_id' => $task->organization_id,
                'type' => 'task_assigned',
                'message' => $request->user()->name . ' assigned you a task: ' . $task->title,
                'data' => ['task_id' => $task->id],
            ]);
        }

        return response()->json($task->load(['project', 'assignedUser', 'creator']), 201);
    }

    public function show(Task $task)
    {
        $task->load(['project', 'assignedUser', 'creator', 'comments.user', 'attachments.uploader']);
        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        if ($task->organization_id !== $request->user()->current_organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:todo,in_progress,review,completed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'order' => 'sometimes|integer',
        ]);

        $oldAssignedTo = $task->assigned_to;
        $task->update($validated);

        if (isset($validated['assigned_to']) && $validated['assigned_to'] !== $oldAssignedTo) {
            Notification::create([
                'user_id' => $validated['assigned_to'],
                'organization_id' => $task->organization_id,
                'type' => 'task_assigned',
                'message' => $request->user()->name . ' assigned you a task: ' . $task->title,
                'data' => ['task_id' => $task->id],
            ]);
        }

        if (isset($validated['status']) && $validated['status'] === 'completed' && $task->assigned_to) {
            Notification::create([
                'user_id' => $task->created_by,
                'organization_id' => $task->organization_id,
                'type' => 'task_completed',
                'message' => 'Task completed: ' . $task->title,
                'data' => ['task_id' => $task->id],
            ]);
        }

        return response()->json($task->load(['project', 'assignedUser', 'creator']));
    }

    public function destroy(Task $task)
    {
        if ($task->organization_id !== request()->user()->current_organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function addComment(Request $request, Task $task)
    {
        if ($task->organization_id !== $request->user()->current_organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        event(new CommentAddedEvent($comment));

        if ($task->assigned_to && $task->assigned_to !== $request->user()->id) {
            Mail::to($task->assignedUser->email)->send(new TaskCommentNotification(
                $task,
                $comment,
                $request->user()
            ));
        }

        if ($task->assigned_to && $task->assigned_to !== $request->user()->id) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'organization_id' => $task->organization_id,
                'type' => 'comment_added',
                'message' => $request->user()->name . ' commented on: ' . $task->title,
                'data' => ['task_id' => $task->id, 'comment_id' => $comment->id],
            ]);
        }

        return response()->json($comment->load('user'), 201);
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.order' => 'required|integer',
            'tasks.*.status' => 'sometimes|in:todo,in_progress,review,completed',
        ]);

        foreach ($validated['tasks'] as $taskData) {
            $task = Task::find($taskData['id']);
            if ($task->organization_id === $request->user()->current_organization_id) {
                $updateData = ['order' => $taskData['order']];
                if (isset($taskData['status'])) {
                    $updateData['status'] = $taskData['status'];
                }
                $task->update($updateData);
            }
        }

        // Check if status changed to completed
        if (isset($validated['status']) && $validated['status'] === 'completed' && $task->wasChanged('status')) {
            if ($task->created_by !== $request->user()->id) {
                event(new TaskCompletedEvent($task, $request->user()));
            }
        }

        // Check if assigned_to changed
        if (isset($validated['assigned_to']) && $validated['assigned_to'] !== $oldAssignedTo && $validated['assigned_to']) {
            event(new TaskAssignedEvent($task, $request->user()));
        }

        return response()->json(['message' => 'Tasks updated successfully']);
    }
}
