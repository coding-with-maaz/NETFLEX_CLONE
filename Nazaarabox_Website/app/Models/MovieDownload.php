<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
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

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}

