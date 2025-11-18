<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\ContentRequest;

class ContentRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public ContentRequest $contentRequest;

    /**
     * Create a new message instance.
     */
    public function __construct(ContentRequest $contentRequest)
    {
        $this->contentRequest = $contentRequest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusLabels = [
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'completed' => 'Completed',
        ];
        
        $statusLabel = $statusLabels[$this->contentRequest->status] ?? $this->contentRequest->status;
        
        return new Envelope(
            subject: 'Content Request Update: ' . $this->contentRequest->title . ' - ' . $statusLabel,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.content-request-notification',
            with: [
                'contentRequest' => $this->contentRequest,
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
