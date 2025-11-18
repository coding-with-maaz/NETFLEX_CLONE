<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'tmdb_id',
        'title',
        'slug',
        'overview',
        'poster_path',
        'backdrop_path',
        'release_date',
        'runtime',
        'vote_average',
        'vote_count',
        'view_count',
        'status',
        'is_featured',
        'featured_order',
        'imdb_id',
        'original_language',
        'tagline',
        'popularity',
        'revenue',
        'budget',
        'category_id',
        'dubbing_language_id',
    ];

    protected $casts = [
        'release_date' => 'date',
        'vote_average' => 'decimal:1',
        'vote_count' => 'integer',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
        'popularity' => 'decimal:2',
        'revenue' => 'decimal:2',
        'budget' => 'decimal:2',
        'runtime' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($movie) {
            if (empty($movie->slug)) {
                $movie->slug = Str::slug($movie->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function dubbingLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'dubbing_language_id');
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'movie_genre', 'movie_id', 'genre_id');
    }

    public function embeds(): HasMany
    {
        return $this->hasMany(MovieEmbed::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(MovieDownload::class);
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

