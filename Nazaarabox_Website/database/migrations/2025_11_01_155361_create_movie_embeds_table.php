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
        Schema::create('movie_embeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->string('server_name'); // e.g., 'Server 1', 'Vidstream', etc.
            $table->string('embed_url');
            $table->integer('priority')->default(0); // Higher priority shown first
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('movie_id');
            $table->index(['movie_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_embeds');
    }
};

