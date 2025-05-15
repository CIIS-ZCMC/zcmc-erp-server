<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The email subject.
     */
    public string $subject;
    
    /**
     * The email content/message.
     */
    public string $emailContent;
    
    /**
     * The URL for the button.
     */
    public ?string $actionUrl;
    
    /**
     * The button text.
     */
    public ?string $actionText;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $emailContent, ?string $actionUrl = null, ?string $actionText = null)
    {
        $this->subject = $subject;
        $this->emailContent = $emailContent;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' - ' . $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.general-notification',
            with: [
                'content' => $this->emailContent,
                'actionUrl' => $this->actionUrl,
                'actionText' => $this->actionText,
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
