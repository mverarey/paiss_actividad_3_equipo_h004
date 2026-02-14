<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes_are_accessible()
    {
        $this->get('/')->assertStatus(200);
        $this->get('/about')->assertStatus(200);
        $this->get('/contact')->assertStatus(200);
    }

    public function test_protected_routes_require_authentication()
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
        $this->post('/posts')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_protected_routes()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertStatus(200);
    }

    public function test_admin_routes_require_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        
        $this->actingAs($user)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);
    }

    public function test_api_routes_return_json()
    {
        $response = $this->getJson('/api/posts');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }
}