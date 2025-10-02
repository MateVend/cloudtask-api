<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskCommentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Task $task,
        public TaskComment $comment,
        public User $commenter
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New comment on: ' . $this->task->title
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.task-comment');
    }
}
