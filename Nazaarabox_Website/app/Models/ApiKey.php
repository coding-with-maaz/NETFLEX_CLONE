<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key_hash',
        'key_prefix',
        'is_active',
        'request_count',
        'last_used_at',
        'expires_at',
        'allowed_ips',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'request_count' => 'integer',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'allowed_ips' => 'array',
    ];

    protected $hidden = [
        'key_hash',
    ];

    /**
     * Generate a new API key
     * 
     * @param string $name Name/description for the key
     * @param array $options Optional settings (expires_at, allowed_ips, notes)
     * @return array ['key' => plain text key, 'apiKey' => ApiKey model]
     */
    public static function generate(string $name, array $options = []): array
    {
        // Generate a secure random key
        $plainKey = 'nzb_api_' . Str::random(32);
        
        // Store prefix (first 8 characters) for identification
        $prefix = substr($plainKey, 0, 8);
        
        // Hash the full key
        $keyHash = Hash::make($plainKey);
        
        // Create the API key record
        $apiKey = self::create([
            'name' => $name,
            'key_hash' => $keyHash,
            'key_prefix' => $prefix,
            'is_active' => $options['is_active'] ?? true,
            'expires_at' => $options['expires_at'] ?? null,
            'allowed_ips' => $options['allowed_ips'] ?? null,
            'notes' => $options['notes'] ?? null,
        ]);

        return [
            'key' => $plainKey, // Only returned once
            'apiKey' => $apiKey,
        ];
    }

    /**
     * Validate an API key
     * 
     * @param string $plainKey Plain text API key
     * @return ApiKey|null
     */
    public static function validate(string $plainKey): ?self
    {
        // Extract prefix for quick lookup
        $prefix = substr($plainKey, 0, 8);
        
        // Find all active keys with matching prefix
        $apiKeys = self::where('key_prefix', $prefix)
            ->where('is_active', true)
            ->get();

        // Check each key
        foreach ($apiKeys as $apiKey) {
            // Check expiration
            if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
                continue;
            }

            // Verify hash
            if (Hash::check($plainKey, $apiKey->key_hash)) {
                // Update usage statistics
                $apiKey->increment('request_count');
                $apiKey->update(['last_used_at' => now()]);
                
                return $apiKey;
            }
        }

        return null;
    }

    /**
     * Check if IP is allowed
     * 
     * @param string $ip IP address to check
     * @return bool
     */
    public function isIpAllowed(string $ip): bool
    {
        // If no IP restrictions, allow all
        if (empty($this->allowed_ips)) {
            return true;
        }

        return in_array($ip, $this->allowed_ips);
    }

    /**
     * Check if key is valid (active and not expired)
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}

