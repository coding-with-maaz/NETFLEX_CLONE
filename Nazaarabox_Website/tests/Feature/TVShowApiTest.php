<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\TVShow;
use App\Models\Genre;
use App\Models\Category;

class TVShowApiTest extends TestCase
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
        
        // Create test category
        $this->category = Category::create([
            'name' => 'TV Shows',
            'slug' => 'tv-shows',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_list_all_tv_shows()
    {
        TVShow::factory()->count(5)->create([
            'status' => 'active',
            'vote_average' => 8.5,
            'vote_count' => 150,
        ]);

        $response = $this->getJson('/api/v1/tvshows');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'tvShows' => [
                             '*' => [
                                 'id',
                                 'name',
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
    public function it_can_get_top_rated_tv_shows()
    {
        TVShow::factory()->create([
            'status' => 'active',
            'vote_average' => 8.5,
            'vote_count' => 150,
        ]);
        
        TVShow::factory()->create([
            'status' => 'active',
            'vote_average' => 6.0,
            'vote_count' => 50,
        ]);

        $response = $this->getJson('/api/v1/tvshows/top-rated?min_rating=7.5&min_votes=100');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);
        
        $data = $response->json('data.tvShows');
        $this->assertCount(1, $data);
        $this->assertEquals(8.5, $data[0]['vote_average']);
    }

    /** @test */
    public function it_can_get_popular_tv_shows()
    {
        TVShow::factory()->create([
            'status' => 'active',
            'popularity' => 85.5,
        ]);

        $response = $this->getJson('/api/v1/tvshows/popular');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'tvShows',
                         'pagination'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                 ]);
    }

    /** @test */
    public function it_can_get_single_tv_show_details()
    {
        $tvShow = TVShow::factory()->create([
            'status' => 'active',
            'name' => 'Test TV Show',
        ]);
        $tvShow->genres()->attach($this->genre1->id);

        $response = $this->getJson("/api/v1/tvshows/{$tvShow->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'name',
                         'slug',
                         'overview',
                         'poster_path',
                         'backdrop_path',
                         'genres',
                         'seasons'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $tvShow->id,
                         'name' => 'Test TV Show',
                     ]
                 ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_tv_show()
    {
        $response = $this->getJson('/api/v1/tvshows/99999');

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                 ]);
    }

    /** @test */
    public function it_only_returns_active_tv_shows()
    {
        TVShow::factory()->create(['status' => 'active']);
        TVShow::factory()->create(['status' => 'inactive']);

        $response = $this->getJson('/api/v1/tvshows');

        $response->assertStatus(200);
        
        $data = $response->json('data.tvShows');
        foreach ($data as $tvShow) {
            $this->assertEquals('active', $tvShow['status']);
        }
    }

    /** @test */
    public function it_formats_tmdb_poster_paths_correctly()
    {
        $tvShow = TVShow::factory()->create([
            'status' => 'active',
            'poster_path' => '/abc123.jpg',
        ]);

        $response = $this->getJson("/api/v1/tvshows/{$tvShow->id}");

        $response->assertStatus(200);
        
        $posterPath = $response->json('data.poster_path');
        $this->assertStringStartsWith('https://image.tmdb.org/t/p/w500', $posterPath);
    }
}

