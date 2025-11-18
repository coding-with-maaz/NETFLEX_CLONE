<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Category;

class MovieApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test genres
        $this->genre1 = Genre::create([
            'name' => 'Action',
            'slug' => 'action',
            'is_active' => true,
        ]);
        
        $this->genre2 = Genre::create([
            'name' => 'Drama',
            'slug' => 'drama',
            'is_active' => true,
        ]);
        
        // Create test category
        $this->category = Category::create([
            'name' => 'Movies',
            'slug' => 'movies',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_list_all_movies()
    {
        Movie::factory()->count(5)->create([
            'status' => 'active',
            'vote_average' => 8.5,
            'vote_count' => 150,
        ]);

        $response = $this->getJson('/api/v1/movies');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'movies' => [
                             '*' => [
                                 'id',
                                 'title',
                                 'slug',
                                 'poster_path',
                                 'backdrop_path',
                                 'vote_average',
                                 'genres',
                                 'pagination'
                             ]
                         ],
                         'pagination'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                 ]);
    }

    /** @test */
    public function it_can_filter_movies_by_genre()
    {
        $movie1 = Movie::factory()->create(['status' => 'active']);
        $movie1->genres()->attach($this->genre1->id);
        
        $movie2 = Movie::factory()->create(['status' => 'active']);
        $movie2->genres()->attach($this->genre2->id);

        $response = $this->getJson('/api/v1/movies?genre=action');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);
        
        $data = $response->json('data.movies');
        $this->assertCount(1, $data);
        $this->assertEquals($movie1->id, $data[0]['id']);
    }

    /** @test */
    public function it_can_filter_movies_by_year()
    {
        Movie::factory()->create([
            'status' => 'active',
            'release_date' => '2023-01-01',
        ]);
        
        Movie::factory()->create([
            'status' => 'active',
            'release_date' => '2024-01-01',
        ]);

        $response = $this->getJson('/api/v1/movies?year=2023');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);
        
        $data = $response->json('data.movies');
        $this->assertCount(1, $data);
        $this->assertEquals('2023-01-01', $data[0]['release_date']);
    }

    /** @test */
    public function it_can_sort_movies_by_rating()
    {
        Movie::factory()->create([
            'status' => 'active',
            'vote_average' => 7.5,
        ]);
        
        Movie::factory()->create([
            'status' => 'active',
            'vote_average' => 9.0,
        ]);

        $response = $this->getJson('/api/v1/movies?sort_by=rating&order=desc');

        $response->assertStatus(200);
        
        $data = $response->json('data.movies');
        $this->assertCount(2, $data);
        $this->assertEquals(9.0, $data[0]['vote_average']);
        $this->assertEquals(7.5, $data[1]['vote_average']);
    }

    /** @test */
    public function it_can_get_top_rated_movies()
    {
        Movie::factory()->create([
            'status' => 'active',
            'vote_average' => 8.5,
            'vote_count' => 150,
        ]);
        
        Movie::factory()->create([
            'status' => 'active',
            'vote_average' => 6.0,
            'vote_count' => 50,
        ]);

        $response = $this->getJson('/api/v1/movies/top-rated?min_rating=7.5&min_votes=100');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'movies',
                         'pagination'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                 ]);
        
        $data = $response->json('data.movies');
        $this->assertCount(1, $data);
        $this->assertEquals(8.5, $data[0]['vote_average']);
    }

    /** @test */
    public function it_can_get_trending_movies()
    {
        Movie::factory()->create([
            'status' => 'active',
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->getJson('/api/v1/movies/trending?period=week');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'movies',
                         'pagination',
                         'period'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'period' => 'week'
                     ]
                 ]);
    }

    /** @test */
    public function it_can_search_movies()
    {
        Movie::factory()->create([
            'status' => 'active',
            'title' => 'The Matrix',
        ]);
        
        Movie::factory()->create([
            'status' => 'active',
            'title' => 'Inception',
        ]);

        $response = $this->getJson('/api/v1/movies/search?q=matrix');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);
        
        $data = $response->json('data.movies');
        $this->assertGreaterThanOrEqual(1, count($data));
        $this->assertStringContainsStringIgnoringCase('matrix', $data[0]['title']);
    }

    /** @test */
    public function it_can_get_single_movie_details()
    {
        $movie = Movie::factory()->create([
            'status' => 'active',
            'title' => 'Test Movie',
        ]);
        $movie->genres()->attach($this->genre1->id);

        $response = $this->getJson("/api/v1/movies/{$movie->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'title',
                         'slug',
                         'overview',
                         'poster_path',
                         'backdrop_path',
                         'genres',
                         'embeds',
                         'downloads'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $movie->id,
                         'title' => 'Test Movie',
                     ]
                 ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_movie()
    {
        $response = $this->getJson('/api/v1/movies/99999');

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                 ]);
    }

    /** @test */
    public function it_only_returns_active_movies()
    {
        Movie::factory()->create(['status' => 'active']);
        Movie::factory()->create(['status' => 'inactive']);

        $response = $this->getJson('/api/v1/movies');

        $response->assertStatus(200);
        
        $data = $response->json('data.movies');
        foreach ($data as $movie) {
            $this->assertEquals('active', $movie['status']);
        }
    }

    /** @test */
    public function it_formats_tmdb_poster_paths_correctly()
    {
        $movie = Movie::factory()->create([
            'status' => 'active',
            'poster_path' => '/abc123.jpg',
        ]);

        $response = $this->getJson("/api/v1/movies/{$movie->id}");

        $response->assertStatus(200);
        
        $posterPath = $response->json('data.poster_path');
        $this->assertStringStartsWith('https://image.tmdb.org/t/p/w500', $posterPath);
    }

    /** @test */
    public function it_supports_pagination()
    {
        Movie::factory()->count(25)->create(['status' => 'active']);

        $response = $this->getJson('/api/v1/movies?page=1&limit=10');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(10, $data['movies']);
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(10, $data['pagination']['per_page']);
        $this->assertTrue($data['pagination']['has_next']);
    }

    /** @test */
    public function it_returns_today_movies()
    {
        Movie::factory()->create([
            'status' => 'active',
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/movies/today/all');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'title',
                             'poster_path'
                         ]
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                 ]);
    }
}

