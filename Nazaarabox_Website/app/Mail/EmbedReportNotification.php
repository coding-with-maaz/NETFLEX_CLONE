<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\EmbedReport;

class EmbedReportNotification extends Mailable
{
    use Queueable, SerializesModels;

    public EmbedReport $embedReport;

    /**
     * Create a new message instance.
     */
    public function __construct(EmbedReport $embedReport)
    {
        $this->embedReport = $embedReport;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusLabels = [
            'pending' => 'Pending Review',
            'reviewed' => 'Reviewed',
            'fixed' => 'Fixed',
            'dismissed' => 'Dismissed',
        ];
        
        $statusLabel = $statusLabels[$this->embedReport->status] ?? $this->embedReport->status;
        
        $contentTitle = 'Content';
        if ($this->embedReport->content) {
            if ($this->embedReport->content_type === 'movie') {
                $contentTitle = $this->embedReport->content->title ?? 'Movie';
            } elseif ($this->embedReport->content_type === 'episode') {
                $episode = $this->embedReport->content;
                if ($episode && $episode->season && $episode->season->tvShow) {
                    $contentTitle = $episode->season->tvShow->name ?? 'TV Show';
                } else {
                    $contentTitle = 'Episode';
                }
            }
        }
        
        return new Envelope(
            subject: 'Embed Report Update: ' . $contentTitle . ' - ' . $statusLabel,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.embed-report-notification',
            with: [
                'embedReport' => $this->embedReport,
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
