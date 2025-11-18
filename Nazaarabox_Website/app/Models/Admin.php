<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Set the password attribute with hashing
     * Only hash if value is not already hashed
     */
    public function setPasswordAttribute($value)
    {
        // Check if value is already hashed (bcrypt hash starts with $2y$)
        if (!empty($value) && !str_starts_with($value, '$2y$') && !str_starts_with($value, '$2a$') && !str_starts_with($value, '$2b$')) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }
}

