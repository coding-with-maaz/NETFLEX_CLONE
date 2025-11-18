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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable'); // Polymorphic: commentable_type, commentable_id (for movies, tv_shows, episodes)
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade'); // For nested replies
            $table->string('name'); // Commenter name
            $table->string('email'); // Commenter email
            $table->text('comment'); // Comment content
            $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])->default('pending'); // Comment status
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null'); // Admin who replied/approved
            $table->boolean('is_admin_reply')->default(false); // Flag for admin replies
            $table->string('ip_address', 45)->nullable(); // IP address for moderation
            $table->string('user_agent')->nullable(); // User agent
            $table->integer('like_count')->default(0); // Like count (for future use)
            $table->integer('dislike_count')->default(0); // Dislike count (for future use)
            $table->timestamps();
            
            // Indexes
            $table->index('commentable_type');
            $table->index('commentable_id');
            $table->index('parent_id');
            $table->index('status');
            $table->index('email');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
