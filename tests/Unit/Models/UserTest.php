<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_posts()
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);
        
        $this->assertCount(3, $user->posts);
        $this->assertInstanceOf(Post::class, $user->posts->first());
    }

    public function test_full_name_accessor()
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
        
        $this->assertEquals('John Doe', $user->full_name);
    }

    public function test_email_is_cast_to_lowercase()
    {
        $user = User::factory()->create(['email' => 'TEST@EXAMPLE.COM']);
        
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_active_scope_returns_only_active_users()
    {
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => false]);
        
        $activeUsers = User::active()->get();
        
        $this->assertCount(1, $activeUsers);
        $this->assertTrue($activeUsers->first()->is_active);
    }
}