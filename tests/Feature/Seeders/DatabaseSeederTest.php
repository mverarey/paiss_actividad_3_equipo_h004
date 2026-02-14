<?php

namespace Tests\Feature\Seeders;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_admin_user()
    {
        $this->seed();
        
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => 'admin'
        ]);
    }

    public function test_database_seeder_creates_categories()
    {
        $this->seed();
        
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseHas('categories', ['name' => 'Technology']);
        $this->assertDatabaseHas('categories', ['name' => 'Business']);
    }

    public function test_database_seeder_creates_posts_with_authors()
    {
        $this->seed();
        
        $posts = Post::all();
        
        $this->assertGreaterThan(0, $posts->count());
        $posts->each(function ($post) {
            $this->assertNotNull($post->user_id);
            $this->assertInstanceOf(User::class, $post->user);
        });
    }

    public function test_specific_seeder_creates_test_data()
    {
        $this->seed(\Database\Seeders\PostSeeder::class);
        
        $this->assertDatabaseCount('posts', 10);
    }

    public function test_seeder_maintains_referential_integrity()
    {
        $this->seed();
        
        $posts = Post::with('user', 'category')->get();
        
        $posts->each(function ($post) {
            $this->assertNotNull($post->user);
            $this->assertNotNull($post->category);
        });
    }
}