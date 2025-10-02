<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $invitedUser,
        public Organization $organization,
        public User $inviter
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->inviter->name . ' invited you to ' . $this->organization->name
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.team-invitation');
    }
}
