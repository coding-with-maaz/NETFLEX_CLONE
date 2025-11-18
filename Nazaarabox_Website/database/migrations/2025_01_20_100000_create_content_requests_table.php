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
        Schema::create('content_requests', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'movie' or 'tvshow'
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('tmdb_id')->nullable(); // Optional TMDB ID if user knows it
            $table->string('year')->nullable(); // Release year
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected', 'completed'
            $table->text('admin_notes')->nullable(); // Admin can add notes
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->integer('request_count')->default(1); // Count how many times this was requested
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            
            $table->index('type');
            $table->index('status');
            $table->index('requested_at');
            $table->index(['type', 'title']); // For searching
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_requests');
    }
};

