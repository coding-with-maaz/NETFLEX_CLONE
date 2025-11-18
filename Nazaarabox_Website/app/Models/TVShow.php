<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class TVShow extends Model
{
    use HasFactory;

    protected $table = 'tv_shows';

    protected $fillable = [
        'tmdb_id',
        'name',
        'slug',
        'overview',
        'poster_path',
        'backdrop_path',
        'first_air_date',
        'last_air_date',
        'number_of_seasons',
        'number_of_episodes',
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
        'episode_run_time',
        'type',
        'category_id',
        'dubbing_language_id',
    ];

    protected $casts = [
        'first_air_date' => 'date',
        'last_air_date' => 'date',
        'vote_average' => 'decimal:1',
        'vote_count' => 'integer',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
        'popularity' => 'decimal:2',
        'number_of_seasons' => 'integer',
        'number_of_episodes' => 'integer',
        'episode_run_time' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tvShow) {
            if (empty($tvShow->slug)) {
                $tvShow->slug = Str::slug($tvShow->name);
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
        return $this->belongsToMany(Genre::class, 'tv_show_genre', 'tv_show_id', 'genre_id');
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class, 'tv_show_id');
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

