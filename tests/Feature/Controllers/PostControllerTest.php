<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_all_posts()
    {
        $posts = Post::factory()->count(5)->create();
        
        $response = $this->get(route('posts.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('posts');
        $response->assertViewHas('posts', function ($collection) {
            return $collection->count() === 5;
        });
    }

    public function test_authenticated_user_can_create_post()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post(route('posts.store'), [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
            'published' => true
        ]);
        
        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'user_id' => $user->id
        ]);
    }

    public function test_guest_cannot_create_post()
    {
        $response = $this->post(route('posts.store'), [
            'title' => 'Test Post',
            'content' => 'Content'
        ]);
        
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_update_own_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        
        $response = $this->actingAs($user)->put(route('posts.update', $post), [
            'title' => 'Updated Title',
            'content' => 'Updated Content'
        ]);
        
        $response->assertRedirect(route('posts.show', $post));
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title'
        ]);
    }

    public function test_user_cannot_delete_other_users_post()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);
        
        $response = $this->actingAs($otherUser)->delete(route('posts.destroy', $post));
        
        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }
}