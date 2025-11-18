<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodeEmbed extends Model
{
    use HasFactory;

    protected $fillable = [
        'episode_id',
        'server_name',
        'embed_url',
        'priority',
        'is_active',
        'requires_ad',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
        'requires_ad' => 'boolean',
    ];

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }
}

