<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'episode_number',
        'name',
        'overview',
        'still_path',
        'air_date',
        'runtime',
        'vote_average',
        'vote_count',
        'view_count',
    ];

    protected $casts = [
        'air_date' => 'date',
        'runtime' => 'integer',
        'vote_average' => 'decimal:1',
        'vote_count' => 'integer',
        'view_count' => 'integer',
        'episode_number' => 'integer',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function embeds(): HasMany
    {
        return $this->hasMany(EpisodeEmbed::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(EpisodeDownload::class);
    }

    public function views(): MorphMany
    {
        return $this->morphMany(View::class, 'viewable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

