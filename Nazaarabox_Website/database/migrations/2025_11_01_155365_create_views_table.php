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
        Schema::create('content_views', function (Blueprint $table) {
            $table->id();
            $table->morphs('viewable'); // Polymorphic: creates viewable_type, viewable_id and their index automatically
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('viewed_at');
            $table->timestamps();
            
            // Only add viewed_at index, morphs() already creates the viewable index
            $table->index('viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_views');
    }
};

