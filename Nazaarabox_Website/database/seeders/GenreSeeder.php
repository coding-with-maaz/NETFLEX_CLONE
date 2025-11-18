<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            ['tmdb_id' => 28, 'name' => 'Action', 'slug' => 'action', 'description' => 'High energy films with lots of action sequences.'],
            ['tmdb_id' => 12, 'name' => 'Adventure', 'slug' => 'adventure', 'description' => 'Exciting journeys and quests.'],
            ['tmdb_id' => 16, 'name' => 'Animation', 'slug' => 'animation', 'description' => 'Animated films and shows.'],
            ['tmdb_id' => null, 'name' => 'Biography', 'slug' => 'biography', 'description' => 'Biographical films and shows.'],
            ['tmdb_id' => 35, 'name' => 'Comedy', 'slug' => 'comedy', 'description' => 'Funny and humorous content.'],
            ['tmdb_id' => 80, 'name' => 'Crime', 'slug' => 'crime', 'description' => 'Stories involving crime and law enforcement.'],
            ['tmdb_id' => 99, 'name' => 'Documentary', 'slug' => 'documentary', 'description' => 'Non-fiction films and shows.'],
            ['tmdb_id' => 18, 'name' => 'Drama', 'slug' => 'drama', 'description' => 'Serious plot-driven presentations.'],
            ['tmdb_id' => 10751, 'name' => 'Family', 'slug' => 'family', 'description' => 'Content suitable for the whole family.'],
            ['tmdb_id' => 14, 'name' => 'Fantasy', 'slug' => 'fantasy', 'description' => 'Stories with magical or supernatural elements.'],
            ['tmdb_id' => null, 'name' => 'Film-Noir', 'slug' => 'film-noir', 'description' => 'Film noir style content.'],
            ['tmdb_id' => null, 'name' => 'Game-Show', 'slug' => 'game-show', 'description' => 'Game show programs.'],
            ['tmdb_id' => 36, 'name' => 'History', 'slug' => 'history', 'description' => 'Historical events and periods.'],
            ['tmdb_id' => 27, 'name' => 'Horror', 'slug' => 'horror', 'description' => 'Scary and suspenseful content.'],
            ['tmdb_id' => 10402, 'name' => 'Music', 'slug' => 'music', 'description' => 'Music-focused films and shows.'],
            ['tmdb_id' => null, 'name' => 'Musical', 'slug' => 'musical', 'description' => 'Musical films and shows.'],
            ['tmdb_id' => 9648, 'name' => 'Mystery', 'slug' => 'mystery', 'description' => 'Puzzle-solving and mysterious plots.'],
            ['tmdb_id' => null, 'name' => 'News', 'slug' => 'news', 'description' => 'News programs.'],
            ['tmdb_id' => null, 'name' => 'Reality-TV', 'slug' => 'reality-tv', 'description' => 'Reality television shows.'],
            ['tmdb_id' => 10749, 'name' => 'Romance', 'slug' => 'romance', 'description' => 'Love stories and romantic relationships.'],
            ['tmdb_id' => 878, 'name' => 'Sci-Fi', 'slug' => 'sci-fi', 'description' => 'Science fiction content.'],
            ['tmdb_id' => null, 'name' => 'Short', 'slug' => 'short', 'description' => 'Short films and content.'],
            ['tmdb_id' => null, 'name' => 'Sport', 'slug' => 'sport', 'description' => 'Sports-related content.'],
            ['tmdb_id' => null, 'name' => 'Talk-Show', 'slug' => 'talk-show', 'description' => 'Talk show programs.'],
            ['tmdb_id' => 53, 'name' => 'Thriller', 'slug' => 'thriller', 'description' => 'Suspenseful and exciting content.'],
            ['tmdb_id' => 10752, 'name' => 'War', 'slug' => 'war', 'description' => 'War-related stories.'],
            ['tmdb_id' => 37, 'name' => 'Western', 'slug' => 'western', 'description' => 'Western-themed content.'],
            ['tmdb_id' => null, 'name' => '18+', 'slug' => '18-plus', 'description' => 'Adult content suitable for viewers 18 years and older.'],
            
            // Drama Sub-genres
            ['tmdb_id' => null, 'name' => 'Psychological Drama', 'slug' => 'psychological-drama', 'description' => 'Dramas exploring psychological themes and mental states.'],
            ['tmdb_id' => null, 'name' => 'Crime Drama', 'slug' => 'crime-drama', 'description' => 'Dramas centered around criminal activities and investigations.'],
            ['tmdb_id' => null, 'name' => 'Political Drama', 'slug' => 'political-drama', 'description' => 'Dramas focused on political themes and government intrigue.'],
            ['tmdb_id' => null, 'name' => 'Legal Drama', 'slug' => 'legal-drama', 'description' => 'Courtroom dramas and legal system stories.'],
            ['tmdb_id' => null, 'name' => 'Medical Drama', 'slug' => 'medical-drama', 'description' => 'Hospital and medical profession dramas.'],
            ['tmdb_id' => null, 'name' => 'Family Drama', 'slug' => 'family-drama', 'description' => 'Family dramas with mature themes and complex relationships.'],
            ['tmdb_id' => null, 'name' => 'Historical Drama', 'slug' => 'historical-drama', 'description' => 'Dramas set in historical periods.'],
            ['tmdb_id' => null, 'name' => 'Tragedy', 'slug' => 'tragedy', 'description' => 'Tragic stories with serious and often heartbreaking outcomes.'],
            ['tmdb_id' => null, 'name' => 'Erotic Drama', 'slug' => 'erotic-drama', 'description' => 'Dramas with explicit adult content and mature themes.'],
            
            // Thriller Sub-genres
            ['tmdb_id' => null, 'name' => 'Erotic Thriller', 'slug' => 'erotic-thriller', 'description' => 'Thrillers with explicit adult content and suspense.'],
            
            // Romance Sub-genres
            ['tmdb_id' => null, 'name' => 'Romantic Tragedy', 'slug' => 'romantic-tragedy', 'description' => 'Love stories with tragic endings.'],
            ['tmdb_id' => null, 'name' => 'Mature Romance', 'slug' => 'mature-romance', 'description' => 'Realistic adult relationships and mature romantic themes.'],
            
            // Comedy Sub-genres
            ['tmdb_id' => null, 'name' => 'Sex Comedy', 'slug' => 'sex-comedy', 'description' => 'Comedies with adult sexual themes and humor.'],
            
            ['tmdb_id' => null, 'name' => 'Other', 'slug' => 'other', 'description' => 'Other genres.'],
        ];

        foreach ($genres as $genre) {
            // Use tmdb_id if available, otherwise use slug
            if ($genre['tmdb_id']) {
                Genre::updateOrCreate(
                    ['tmdb_id' => $genre['tmdb_id']],
                    $genre
                );
            } else {
                Genre::updateOrCreate(
                    ['slug' => $genre['slug']],
                    $genre
                );
            }
        }
    }
}

