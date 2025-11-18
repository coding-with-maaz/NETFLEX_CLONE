<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'tmdb_id',
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'movie_genre', 'genre_id', 'movie_id');
    }

    public function tvShows(): BelongsToMany
    {
        return $this->belongsToMany(TVShow::class, 'tv_show_genre', 'genre_id', 'tv_show_id');
    }
}

