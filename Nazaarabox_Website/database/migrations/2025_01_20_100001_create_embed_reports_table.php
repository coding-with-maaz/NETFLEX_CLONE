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
        Schema::create('embed_reports', function (Blueprint $table) {
            $table->id();
            $table->string('content_type'); // 'movie' or 'episode'
            $table->unsignedBigInteger('content_id'); // movie_id or episode_id
            $table->unsignedBigInteger('embed_id')->nullable(); // Specific embed that has problem
            $table->string('report_type'); // 'not_working', 'wrong_content', 'poor_quality', 'broken_link', 'other'
            $table->text('description')->nullable(); // User's description of the problem
            $table->string('status')->default('pending'); // 'pending', 'reviewed', 'fixed', 'dismissed'
            $table->text('admin_notes')->nullable(); // Admin can add notes
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->integer('report_count')->default(1); // Count how many times this was reported
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            
            $table->index('content_type');
            $table->index(['content_type', 'content_id']);
            $table->index('status');
            $table->index('report_type');
            $table->index('reported_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embed_reports');
    }
};

