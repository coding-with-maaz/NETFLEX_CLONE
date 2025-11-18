<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodeDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'episode_id',
        'quality',
        'server_name',
        'download_url',
        'size',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }
}

