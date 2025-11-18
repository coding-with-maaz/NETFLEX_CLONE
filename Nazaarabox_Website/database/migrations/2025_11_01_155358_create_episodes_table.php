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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->integer('episode_number');
            $table->string('name')->nullable();
            $table->text('overview')->nullable();
            $table->string('still_path')->nullable();
            $table->date('air_date')->nullable();
            $table->integer('runtime')->nullable(); // in minutes
            $table->decimal('vote_average', 3, 1)->default(0);
            $table->integer('vote_count')->default(0);
            $table->bigInteger('view_count')->default(0);
            $table->timestamps();
            
            $table->unique(['season_id', 'episode_number']);
            $table->index('air_date');
            $table->index('view_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};

