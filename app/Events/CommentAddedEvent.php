<?php

namespace App\Events;

use App\Models\TaskComment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentAddedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public TaskComment $comment) {}

    public function broadcastOn(): array
    {
        $task = $this->comment->task;

        return [
            new Channel('task.' . $task->id),
            new Channel('user.' . $task->assigned_to),
        ];
    }

    public function broadcastAs(): string
    {
        return 'comment.added';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => uniqid(),
            'comment' => $this->comment->load(['user', 'task']),
            'message' => $this->comment->user->name . ' commented on: ' . $this->comment->task->title,
            'type' => 'comment_added',
            'created_at' => now()->toISOString(),
        ];
    }
}
