<?php

namespace App\Mail;

use App\Models\AopApplication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The application instance.
     */
    public AopApplication $application;
    
    /**
     * The notification title.
     */
    public string $title;
    
    /**
     * The notification message.
     */
    public string $message;
    
    /**
     * The user who performed the action.
     */
    public ?User $actionUser;

    /**
     * Create a new message instance.
     */
    public function __construct(
        AopApplication $application, 
        string $title, 
        string $message, 
        ?User $actionUser = null
    ) {
        $this->application = $application;
        $this->title = $title;
        $this->message = $message;
        $this->actionUser = $actionUser;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' - ' . $this->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.approval-notification',
            with: [
                'application' => $this->application,
                'title' => $this->title,
                'message' => $this->message,
                'actionUser' => $this->actionUser,
                'url' => url('/aop-application/' . $this->application->id),
                'appName' => config('app.name'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
