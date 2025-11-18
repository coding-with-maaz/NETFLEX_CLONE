<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieEmbed extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'server_name',
        'embed_url',
        'language_id',
        'priority',
        'is_active',
        'requires_ad',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
        'requires_ad' => 'boolean',
    ];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}

