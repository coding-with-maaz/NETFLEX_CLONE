<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name/description of the API key (e.g., "Flutter App", "Mobile App")
            $table->string('key_hash'); // Hashed API key (stored securely)
            $table->string('key_prefix', 8); // First 8 characters of the key for identification (e.g., "nzb_api_")
            $table->boolean('is_active')->default(true);
            $table->integer('request_count')->default(0); // Track API usage
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Optional expiration
            $table->json('allowed_ips')->nullable(); // Optional IP whitelist
            $table->text('notes')->nullable(); // Optional notes
            $table->timestamps();
            
            $table->index('key_prefix');
            $table->index('is_active');
            $table->index('key_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};

