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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id')->unique()->nullable(); // TMDB movie ID
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->date('release_date')->nullable();
            $table->integer('runtime')->nullable(); // in minutes
            $table->decimal('vote_average', 3, 1)->default(0); // 0.0 to 10.0
            $table->integer('vote_count')->default(0);
            $table->bigInteger('view_count')->default(0);
            $table->string('status')->default('active'); // 'active', 'inactive', 'upcoming'
            $table->boolean('is_featured')->default(false);
            $table->string('imdb_id')->nullable();
            $table->string('original_language', 5)->nullable();
            $table->text('tagline')->nullable();
            $table->decimal('popularity', 10, 2)->default(0);
            $table->decimal('revenue', 15, 2)->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            
            $table->index('status');
            $table->index('is_featured');
            $table->index('release_date');
            $table->index('view_count');
            $table->index('vote_average');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};

