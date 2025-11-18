<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['code' => 'fr', 'name' => 'French dub', 'native_name' => 'French dub', 'slug' => 'french-dub'],
            ['code' => 'hi', 'name' => 'Hindi dub', 'native_name' => 'Hindi dub', 'slug' => 'hindi-dub'],
            ['code' => 'bn', 'name' => 'Bengali dub', 'native_name' => 'Bengali dub', 'slug' => 'bengali-dub'],
            ['code' => 'ur', 'name' => 'Urdu dub', 'native_name' => 'Urdu dub', 'slug' => 'urdu-dub'],
            ['code' => 'pa', 'name' => 'Punjabi dub', 'native_name' => 'Punjabi dub', 'slug' => 'punjabi-dub'],
            ['code' => 'ta', 'name' => 'Tamil dub', 'native_name' => 'Tamil dub', 'slug' => 'tamil-dub'],
            ['code' => 'te', 'name' => 'Telugu dub', 'native_name' => 'Telugu dub', 'slug' => 'telugu-dub'],
            ['code' => 'ml', 'name' => 'Malayalam dub', 'native_name' => 'Malayalam dub', 'slug' => 'malayalam-dub'],
            ['code' => 'kn', 'name' => 'Kannada dub', 'native_name' => 'Kannada dub', 'slug' => 'kannada-dub'],
            ['code' => 'ar', 'name' => 'Arabic dub', 'native_name' => 'Arabic dub', 'slug' => 'arabic-dub'],
            ['code' => 'tl', 'name' => 'Tagalog dub', 'native_name' => 'Tagalog dub', 'slug' => 'tagalog-dub'],
            ['code' => 'id', 'name' => 'Indonesian dub', 'native_name' => 'Indonesian dub', 'slug' => 'indonesian-dub'],
            ['code' => 'ru', 'name' => 'Russian dub', 'native_name' => 'Russian dub', 'slug' => 'russian-dub'],
            ['code' => 'ku', 'name' => 'Kurdish sub', 'native_name' => 'Kurdish sub', 'slug' => 'kurdish-sub'],
        ];

        foreach ($languages as $language) {
            // Check if a language with this code already exists
            $existing = Language::where('code', $language['code'])->first();
            if ($existing) {
                // Update existing language
                $existing->update($language);
            } else {
                // Create new language
                Language::updateOrCreate(
                    ['slug' => $language['slug']],
                    $language
                );
            }
        }
    }
}

