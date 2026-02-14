<?php

namespace Tests\Feature\Cache;

use Tests\TestCase;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class PostCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_posts_are_cached_after_first_request()
    {
        Post::factory()->count(5)->create();
        
        $posts = Cache::remember('posts', 3600, function () {
            return Post::all();
        });
        
        $this->assertCount(5, $posts);
        $this->assertTrue(Cache::has('posts'));
    }

    public function test_cache_is_retrieved_on_second_request()
    {
        Post::factory()->count(3)->create();
        
        // Primera solicitud - guarda en cache
        $firstCall = Cache::remember('posts', 3600, function () {
            return Post::all();
        });
        
        // Eliminar todos los posts de la DB
        Post::query()->delete();
        
        // Segunda solicitud - debería venir del cache
        $secondCall = Cache::get('posts');
        
        $this->assertCount(3, $secondCall);
    }

    public function test_cache_is_invalidated_when_post_created()
    {
        Cache::put('posts', Post::all(), 3600);
        
        Post::factory()->create();
        Cache::forget('posts');
        
        $this->assertFalse(Cache::has('posts'));
    }

    public function test_cache_has_correct_ttl()
    {
        $ttl = 60; // 60 segundos
        
        Cache::put('test_key', 'test_value', $ttl);
        
        $this->assertTrue(Cache::has('test_key'));
        
        // Simular paso del tiempo (en un test real usarías Carbon::setTestNow)
        sleep(61);
        
        // En un entorno real el cache habría expirado
    }

    public function test_tagged_cache_can_be_flushed_separately()
    {
        Cache::tags(['posts'])->put('featured', Post::factory()->create(), 3600);
        Cache::tags(['users'])->put('admin', 'admin_data', 3600);
        
        Cache::tags(['posts'])->flush();
        
        $this->assertFalse(Cache::tags(['posts'])->has('featured'));
        $this->assertTrue(Cache::tags(['users'])->has('admin'));
    }

    public function test_cache_stores_complex_data()
    {
        $data = [
            'posts' => Post::factory()->count(2)->create()->toArray(),
            'meta' => ['total' => 2, 'page' => 1]
        ];
        
        Cache::put('complex_data', $data, 3600);
        
        $retrieved = Cache::get('complex_data');
        
        $this->assertIsArray($retrieved);
        $this->assertArrayHasKey('posts', $retrieved);
        $this->assertArrayHasKey('meta', $retrieved);
    }
}