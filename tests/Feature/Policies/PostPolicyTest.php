<?php

namespace Tests\Feature\Policies;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_any_posts()
    {
        $user = User::factory()->create();
        
        $this->assertTrue($user->can('viewAny', Post::class));
    }

    public function test_user_can_view_published_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['published' => true]);
        
        $this->assertTrue($user->can('view', $post));
    }

    public function test_user_cannot_view_unpublished_post_from_others()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $otherUser->id,
            'published' => false
        ]);
        
        $this->assertFalse($user->can('view', $post));
    }

    public function test_user_can_create_post()
    {
        $user = User::factory()->create();
        
        $this->assertTrue($user->can('create', Post::class));
    }

    public function test_user_can_update_own_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        
        $this->assertTrue($user->can('update', $post));
    }

    public function test_user_cannot_update_others_post()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);
        
        $this->assertFalse($user->can('update', $post));
    }

    public function test_admin_can_delete_any_post()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $post = Post::factory()->create();
        
        $this->assertTrue($admin->can('delete', $post));
    }

    public function test_user_can_delete_own_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        
        $this->assertTrue($user->can('delete', $post));
    }
}