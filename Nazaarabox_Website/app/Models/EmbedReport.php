<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmbedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_type',
        'content_id',
        'embed_id',
        'report_type',
        'description',
        'email',
        'status',
        'admin_notes',
        'ip_address',
        'user_agent',
        'report_count',
        'reported_at',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'report_count' => 'integer',
        'reported_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    public function content()
    {
        if ($this->content_type === 'movie') {
            return $this->belongsTo(Movie::class, 'content_id');
        } elseif ($this->content_type === 'episode') {
            return $this->belongsTo(Episode::class, 'content_id');
        }
        return null;
    }

    public function embed()
    {
        if ($this->content_type === 'movie' && $this->embed_id) {
            return $this->belongsTo(MovieEmbed::class, 'embed_id');
        } elseif ($this->content_type === 'episode' && $this->embed_id) {
            return $this->belongsTo(EpisodeEmbed::class, 'embed_id');
        }
        return null;
    }
}

