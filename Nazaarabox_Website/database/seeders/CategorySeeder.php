<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'K-Drama', 'slug' => 'k-drama', 'type' => 'k-drama', 'description' => 'Korean drama series'],
            ['name' => 'Anime', 'slug' => 'anime', 'type' => 'anime', 'description' => 'Japanese animated series'],
            ['name' => 'Action', 'slug' => 'action', 'type' => 'general', 'description' => 'Action-packed content'],
            ['name' => 'Comedy', 'slug' => 'comedy', 'type' => 'general', 'description' => 'Comedy content'],
            ['name' => 'Drama', 'slug' => 'drama', 'type' => 'general', 'description' => 'Dramatic content'],
            ['name' => 'Romance', 'slug' => 'romance', 'type' => 'general', 'description' => 'Romantic content'],
            ['name' => 'Thriller', 'slug' => 'thriller', 'type' => 'general', 'description' => 'Thrilling content'],
            ['name' => 'Horror', 'slug' => 'horror', 'type' => 'general', 'description' => 'Horror content'],
            ['name' => 'Sci-Fi', 'slug' => 'sci-fi', 'type' => 'general', 'description' => 'Science fiction content'],
            ['name' => 'Fantasy', 'slug' => 'fantasy', 'type' => 'general', 'description' => 'Fantasy content'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

