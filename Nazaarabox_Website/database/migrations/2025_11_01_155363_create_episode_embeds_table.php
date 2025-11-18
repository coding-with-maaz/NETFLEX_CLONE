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
        Schema::create('episode_embeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('episode_id')->constrained()->cascadeOnDelete();
            $table->string('server_name');
            $table->string('embed_url');
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('episode_id');
            $table->index(['episode_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episode_embeds');
    }
};

