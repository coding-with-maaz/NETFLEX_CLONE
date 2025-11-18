<?php

namespace App\Mail;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentReplyNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $reply;
    public $parentComment;
    public $content;

    /**
     * Create a new message instance.
     */
    public function __construct(Comment $reply, Comment $parentComment, $content)
    {
        $this->reply = $reply;
        $this->parentComment = $parentComment;
        $this->content = $content;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $contentTitle = $this->getContentTitle();
        return new Envelope(
            subject: "New Reply to Your Comment on {$contentTitle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.comment-reply',
            with: [
                'reply' => $this->reply,
                'parentComment' => $this->parentComment,
                'content' => $this->content,
                'contentTitle' => $this->getContentTitle(),
                'contentUrl' => $this->getContentUrl(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get content title
     */
    private function getContentTitle()
    {
        if ($this->content instanceof \App\Models\Movie) {
            return $this->content->title;
        } elseif ($this->content instanceof \App\Models\TVShow) {
            return $this->content->name;
        } elseif ($this->content instanceof \App\Models\Episode) {
            return $this->content->name . ' - ' . ($this->content->season->tvShow->name ?? 'TV Show');
        }
        return 'Content';
    }

    /**
     * Get content URL
     */
    private function getContentUrl()
    {
        $baseUrl = config('app.url');
        
        if ($this->content instanceof \App\Models\Movie) {
            return $baseUrl . '/movies/' . $this->content->id . '#comments-section';
        } elseif ($this->content instanceof \App\Models\TVShow) {
            return $baseUrl . '/tvshows/' . $this->content->id . '#comments-section';
        } elseif ($this->content instanceof \App\Models\Episode) {
            $tvShowId = $this->content->season->tvShow->id ?? null;
            return $baseUrl . '/tvshows/' . $tvShowId . '#comments-section';
        }
        return $baseUrl;
    }
}
