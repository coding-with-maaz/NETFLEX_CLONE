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
        Schema::table('movies', function (Blueprint $table) {
            $table->integer('featured_order')->nullable()->after('is_featured');
            $table->index('featured_order');
        });

        Schema::table('tv_shows', function (Blueprint $table) {
            $table->integer('featured_order')->nullable()->after('is_featured');
            $table->index('featured_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropIndex(['featured_order']);
            $table->dropColumn('featured_order');
        });

        Schema::table('tv_shows', function (Blueprint $table) {
            $table->dropIndex(['featured_order']);
            $table->dropColumn('featured_order');
        });
    }
};
