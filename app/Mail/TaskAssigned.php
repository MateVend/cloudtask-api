<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Task $task,
        public User $assigner
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Task Assigned: ' . $this->task->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task-assigned',
            with: [
                'taskTitle' => $this->task->title,
                'taskDescription' => $this->task->description,
                'priority' => $this->task->priority,
                'dueDate' => $this->task->due_date?->format('M d, Y'),
                'assignerName' => $this->assigner->name,
                'projectName' => $this->task->project->name,
            ],
        );
    }
}
