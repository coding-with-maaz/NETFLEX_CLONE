<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'tv_show_id',
        'season_number',
        'name',
        'overview',
        'poster_path',
        'air_date',
        'episode_count',
    ];

    protected $casts = [
        'air_date' => 'date',
        'season_number' => 'integer',
        'episode_count' => 'integer',
    ];

    public function tvShow(): BelongsTo
    {
        return $this->belongsTo(TVShow::class, 'tv_show_id');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }
}

