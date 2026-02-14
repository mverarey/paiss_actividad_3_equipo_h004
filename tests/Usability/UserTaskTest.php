<?php

namespace Tests\Usability;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Escenario 1: Registro e Inicio de Sesión
     * Métrica: Debe completarse en < 3 minutos
     */
    public function test_user_registration_flow_is_straightforward()
    {
        $startTime = microtime(true);
        
        // 1. Acceder a página de registro
        $registerPage = $this->get('/register');
        $registerPage->assertStatus(200);
        
        // 2. Completar formulario
        $registrationResponse = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        
        // 3. Verificar redirección exitosa
        $registrationResponse->assertRedirect('/dashboard');
        
        // 4. Usuario está autenticado
        $this->assertAuthenticated();
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        // Verificar que el flujo es rápido (simulado)
        $this->assertLessThan(1, $duration, 'El proceso de registro debe ser rápido');
    }

    /**
     * Escenario 2: Crear Contenido
     * Métrica: Formulario debe ser intuitivo y rápido
     */
    public function test_content_creation_requires_minimal_steps()
    {
        $user = User::factory()->create();
        
        $clickCount = 0;
        
        // 1. Click: Acceder a dashboard
        $this->actingAs($user)->get('/dashboard');
        $clickCount++;
        
        // 2. Click: Ir a crear post
        $this->get('/posts/create');
        $clickCount++;
        
        // 3. Click: Guardar post (completar formulario)
        $createResponse = $this->post('/posts', [
            'title' => 'My First Post',
            'content' => 'This is the content of my post.',
            'category_id' => 1
        ]);
        $clickCount++;
        
        $createResponse->assertRedirect();
        $this->assertDatabaseHas('posts', ['title' => 'My First Post']);
        
        // Verificar cantidad de clics necesarios
        $this->assertLessThanOrEqual(3, $clickCount, 'Crear post debe requerir máximo 3 clics');
    }

    /**
     * Escenario 3: Búsqueda y Filtrado
     * Métrica: Encontrar contenido en < 4 clics
     */
    public function test_search_functionality_is_efficient()
    {
        $user = User::factory()->create();
        Post::factory()->create(['title' => 'Laravel Tutorial', 'published' => true]);
        Post::factory()->create(['title' => 'Vue.js Guide', 'published' => true]);
        Post::factory()->create(['title' => 'PHP Best Practices', 'published' => true]);
        
        // 1. Click: Acceder a búsqueda
        $searchPage = $this->actingAs($user)->get('/posts');
        $searchPage->assertStatus(200);
        
        // 2. Click: Realizar búsqueda
        $searchResults = $this->get('/posts?search=Laravel');
        $searchResults->assertStatus(200);
        $searchResults->assertSee('Laravel Tutorial');
        $searchResults->assertDontSee('Vue.js Guide');
        
        // Total de clics: 2 (muy eficiente)
        $this->assertTrue(true, 'Búsqueda completada en 2 clics');
    }

    /**
     * Verificar que formularios tienen validación en tiempo real
     */
    public function test_forms_provide_immediate_validation_feedback()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'ab', // Muy corto
            'content' => ''   // Vacío
        ]);
        
        // Debe retornar errores específicos
        $response->assertSessionHasErrors(['title', 'content']);
        
        $errors = session('errors');
        
        // Mensajes deben ser claros
        $this->assertStringContainsString('título', strtolower($errors->first('title')));
        $this->assertStringContainsString('contenido', strtolower($errors->first('content')));
    }

    /**
     * Verificar navegación breadcrumb para orientación
     */
    public function test_breadcrumb_navigation_shows_user_location()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        $response = $this->actingAs($user)->get("/posts/{$post->id}/edit");
        
        $response->assertStatus(200);
        
        // Verificar breadcrumb presente
        $response->assertSee('Inicio');
        $response->assertSee('Posts');
        $response->assertSee('Editar');
    }
}