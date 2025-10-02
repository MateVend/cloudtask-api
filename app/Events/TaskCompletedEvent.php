<?php

namespace App\Events;

use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCompletedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Task $task,
        public User $completedBy
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('user.' . $this->task->created_by),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => uniqid(),
            'task' => $this->task->load('project'),
            'completed_by' => $this->completedBy->only(['id', 'name', 'avatar']),
            'message' => 'Task completed: ' . $this->task->title,
            'type' => 'task_completed',
            'created_at' => now()->toISOString(),
        ];
    }
}
