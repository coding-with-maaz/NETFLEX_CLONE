<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'email',
        'description',
        'tmdb_id',
        'year',
        'status',
        'admin_notes',
        'ip_address',
        'user_agent',
        'request_count',
        'requested_at',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'request_count' => 'integer',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }
}

