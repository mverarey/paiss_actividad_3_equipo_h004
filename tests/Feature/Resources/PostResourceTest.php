<?php

namespace Tests\Feature\Resources;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Http\Resources\PostResource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_has_correct_structure()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        
        $resource = new PostResource($post);
        $array = $resource->toArray(request());
        
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('content', $array);
        $this->assertArrayHasKey('author', $array);
        $this->assertArrayHasKey('created_at', $array);
    }

    public function test_resource_returns_correct_data()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Post'
        ]);
        
        $resource = new PostResource($post);
        $array = $resource->toArray(request());
        
        $this->assertEquals('Test Post', $array['title']);
        $this->assertEquals('John Doe', $array['author']['name']);
    }

    public function test_resource_collection_structure()
    {
        $posts = Post::factory()->count(3)->create();
        
        $response = $this->getJson('/api/posts');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'content',
                        'author' => ['id', 'name'],
                        'created_at'
                    ]
                ]
            ]);
    }

    public function test_resource_includes_relationships_when_loaded()
    {
        $post = Post::factory()->create();
        $post->load('user', 'comments');
        
        $resource = new PostResource($post);
        $array = $resource->toArray(request());
        
        $this->assertArrayHasKey('comments', $array);
        $this->assertIsArray($array['comments']);
    }

    public function test_resource_formats_dates_correctly()
    {
        $post = Post::factory()->create();
        
        $resource = new PostResource($post);
        $array = $resource->toArray(request());
        
        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/',
            $array['created_at']
        );
    }
}