<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class PostObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_post_generates_slug()
    {
        $post = Post::create([
            'title' => 'My Awesome Post',
            'content' => 'Content here',
            'user_id' => User::factory()->create()->id
        ]);
        
        $this->assertEquals('my-awesome-post', $post->slug);
    }

    public function test_updating_post_title_updates_slug()
    {
        $post = Post::factory()->create(['title' => 'Original Title']);
        
        $post->update(['title' => 'Updated Title']);
        
        $this->assertEquals('updated-title', $post->fresh()->slug);
    }

    public function test_deleting_post_soft_deletes_comments()
    {
        $post = Post::factory()->create();
        $comment = $post->comments()->create([
            'content' => 'Test comment',
            'user_id' => User::factory()->create()->id
        ]);
        
        $post->delete();
        
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_created_event_sends_notification()
    {
        Event::fake();
        
        $post = Post::factory()->create();
        
        Event::assertDispatched(\App\Events\PostCreated::class);
    }

    public function test_saving_post_updates_timestamps()
    {
        $post = Post::factory()->create();
        $originalUpdatedAt = $post->updated_at;
        
        sleep(1);
        $post->update(['title' => 'New Title']);
        
        $this->assertNotEquals($originalUpdatedAt, $post->fresh()->updated_at);
    }
}