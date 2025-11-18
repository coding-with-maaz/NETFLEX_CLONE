<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'parent_id',
        'name',
        'email',
        'comment',
        'status',
        'admin_id',
        'is_admin_reply',
        'ip_address',
        'user_agent',
        'like_count',
        'dislike_count',
    ];

    protected $casts = [
        'is_admin_reply' => 'boolean',
        'like_count' => 'integer',
        'dislike_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent model (Movie, TVShow, or Episode)
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent comment (for nested replies)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get all replies to this comment
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->where('status', 'approved')->orderBy('created_at', 'asc');
    }

    /**
     * Get all replies including pending (for admin)
     */
    public function allReplies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    /**
     * Get the admin who replied/approved
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Scope to get approved comments only
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get pending comments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get top-level comments (no parent)
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get nested replies in a tree structure
     */
    public function getNestedReplies()
    {
        $replies = $this->replies()->get();
        
        foreach ($replies as $reply) {
            $reply->nested_replies = $reply->getNestedReplies();
        }
        
        return $replies;
    }
}
