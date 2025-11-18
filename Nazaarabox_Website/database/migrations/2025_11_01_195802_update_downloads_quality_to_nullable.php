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
        Schema::table('movie_downloads', function (Blueprint $table) {
            $table->string('quality')->nullable()->change();
        });
        
        Schema::table('episode_downloads', function (Blueprint $table) {
            $table->string('quality')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_downloads', function (Blueprint $table) {
            $table->string('quality')->nullable(false)->change();
        });
        
        Schema::table('episode_downloads', function (Blueprint $table) {
            $table->string('quality')->nullable(false)->change();
        });
    }
};

