<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_data_persists()
    {
        $response = $this->withSession(['user_preference' => 'dark_mode'])
            ->get('/dashboard');
        
        $this->assertEquals('dark_mode', session('user_preference'));
    }

    public function test_flash_data_is_available_once()
    {
        $response = $this->get('/set-flash');
        
        $this->assertEquals('Success!', session('message'));
        
        // Simular siguiente request
        session()->forget('message');
        $this->assertNull(session('message'));
    }

    public function test_session_regenerates_on_login()
    {
        $user = User::factory()->create();
        
        $firstSessionId = session()->getId();
        
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $secondSessionId = session()->getId();
        
        $this->assertNotEquals($firstSessionId, $secondSessionId);
    }

    public function test_old_input_is_flashed_on_validation_error()
    {
        $response = $this->post('/posts', [
            'title' => 'Test'
        ]);
        
        $response->assertSessionHasErrors();
        $this->assertEquals('Test', old('title'));
    }

    public function test_session_is_cleared_on_logout()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->withSession(['cart' => ['item1', 'item2']])
            ->post('/logout');
        
        $this->assertNull(session('cart'));
    }

    public function test_csrf_token_is_present_in_session()
    {
        $response = $this->get('/');
        
        $this->assertNotNull(session()->token());
    }
}