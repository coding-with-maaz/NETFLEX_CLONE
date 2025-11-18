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
        Schema::create('movie_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->string('quality'); // '720p', '1080p', '4K', etc.
            $table->string('server_name'); // e.g., 'Mega', 'Google Drive', etc.
            $table->string('download_url');
            $table->string('size')->nullable(); // e.g., '1.2 GB'
            $table->integer('priority')->default(0);
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
        Schema::dropIfExists('movie_downloads');
    }
};

