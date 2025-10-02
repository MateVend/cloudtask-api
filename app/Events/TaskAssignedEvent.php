<?php

namespace App\Events;

use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskAssignedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Task $task,
        public User $assignedBy
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('user.' . $this->task->assigned_to),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => uniqid(),
            'task' => $this->task->load(['project', 'assignedUser']),
            'assigned_by' => $this->assignedBy->only(['id', 'name', 'avatar']),
            'message' => $this->assignedBy->name . ' assigned you a task: ' . $this->task->title,
            'type' => 'task_assigned',
            'created_at' => now()->toISOString(),
        ];
    }
}
