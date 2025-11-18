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
        Schema::table('movie_embeds', function (Blueprint $table) {
            $table->foreignId('language_id')->nullable()->after('server_name')->constrained('languages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_embeds', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropColumn('language_id');
        });
    }
};

