<?php

namespace Tests\Integration\Api;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_token_authentication_flow()
    {
        // 1. Crear usuario
        $user = User::factory()->create([
            'email' => 'api@example.com',
            'password' => bcrypt('password123')
        ]);
        
        // 2. Login y obtener token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'api@example.com',
            'password' => 'password123'
        ]);
        
        $loginResponse->assertStatus(200);
        $loginResponse->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email']
        ]);
        
        $token = $loginResponse->json('token');
        
        // 3. Usar token para acceder a ruta protegida
        $protectedResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');
        
        $protectedResponse->assertStatus(200);
        $protectedResponse->assertJson([
            'email' => 'api@example.com'
        ]);
        
        // 4. Acceder a recurso sin token (debe fallar)
        $unauthorizedResponse = $this->getJson('/api/user');
        $unauthorizedResponse->assertStatus(401);
        
        // 5. Logout (revocar token)
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');
        
        $logoutResponse->assertStatus(200);
        
        // 6. Intentar usar token revocado
        $revokedResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');
        
        $revokedResponse->assertStatus(401);
    }

    public function test_api_crud_operations_with_authentication()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );
        
        // 1. CREATE - Crear recurso
        $createResponse = $this->postJson('/api/posts', [
            'title' => 'API Test Post',
            'content' => 'Content from API test'
        ]);
        
        $createResponse->assertStatus(201);
        $createResponse->assertJsonStructure([
            'data' => ['id', 'title', 'content', 'created_at']
        ]);
        
        $postId = $createResponse->json('data.id');
        
        // 2. READ - Obtener recurso
        $readResponse = $this->getJson("/api/posts/{$postId}");
        $readResponse->assertStatus(200);
        $readResponse->assertJson([
            'data' => [
                'id' => $postId,
                'title' => 'API Test Post'
            ]
        ]);
        
        // 3. UPDATE - Actualizar recurso
        $updateResponse = $this->putJson("/api/posts/{$postId}", [
            'title' => 'Updated API Post',
            'content' => 'Updated content'
        ]);
        
        $updateResponse->assertStatus(200);
        $updateResponse->assertJson([
            'data' => [
                'title' => 'Updated API Post'
            ]
        ]);
        
        // 4. DELETE - Eliminar recurso
        $deleteResponse = $this->deleteJson("/api/posts/{$postId}");
        $deleteResponse->assertStatus(204);
        
        // 5. Verificar que fue eliminado
        $verifyResponse = $this->getJson("/api/posts/{$postId}");
        $verifyResponse->assertStatus(404);
    }

    public function test_api_rate_limiting()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        // Hacer múltiples requests
        for ($i = 0; $i < 60; $i++) {
            $response = $this->getJson('/api/posts');
        }
        
        // El request 61 debería ser bloqueado
        $rateLimitedResponse = $this->getJson('/api/posts');
        $rateLimitedResponse->assertStatus(429); // Too Many Requests
    }

    public function test_api_pagination_and_filtering()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        // Crear múltiples posts
        \App\Models\Post::factory()->count(25)->create([
            'user_id' => $user->id
        ]);
        
        // 1. Paginación básica
        $page1Response = $this->getJson('/api/posts?page=1&per_page=10');
        $page1Response->assertStatus(200);
        $page1Response->assertJsonCount(10, 'data');
        $page1Response->assertJsonStructure([
            'data',
            'links',
            'meta' => ['current_page', 'total', 'per_page']
        ]);
        
        // 2. Filtrado
        $filterResponse = $this->getJson('/api/posts?filter[published]=true');
        $filterResponse->assertStatus(200);
        
        // 3. Ordenamiento
        $sortResponse = $this->getJson('/api/posts?sort=-created_at');
        $sortResponse->assertStatus(200);
        
        // 4. Búsqueda
        $searchResponse = $this->getJson('/api/posts?search=test');
        $searchResponse->assertStatus(200);
    }
}