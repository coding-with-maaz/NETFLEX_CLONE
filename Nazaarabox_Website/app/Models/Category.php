<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
    }

    public function tvShows(): HasMany
    {
        return $this->hasMany(TVShow::class);
    }
}

