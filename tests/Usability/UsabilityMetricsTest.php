<?php

namespace Tests\Usability;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsabilityMetricsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Métrica 1: Tiempo de Carga (Performance)
     * Objetivo: < 3 segundos para páginas principales
     */
    public function test_page_load_time_is_acceptable()
    {
        $user = User::factory()->create();
        
        $startTime = microtime(true);
        $response = $this->actingAs($user)->get('/dashboard');
        $endTime = microtime(true);
        
        $loadTime = $endTime - $startTime;
        
        $response->assertStatus(200);
        $this->assertLessThan(3, $loadTime, 'Dashboard debe cargar en menos de 3 segundos');
    }

    /**
     * Métrica 2: Tasa de Éxito en Tareas
     * Objetivo: > 90% de tareas completadas exitosamente
     */
    public function test_task_completion_rate()
    {
        $successfulTasks = 0;
        $totalTasks = 10;
        
        $user = User::factory()->create();
        
        // Tarea 1: Ver dashboard
        try {
            $this->actingAs($user)->get('/dashboard')->assertStatus(200);
            $successfulTasks++;
        } catch (\Exception $e) {}
        
        // Tarea 2: Crear post
        try {
            $this->post('/posts', [
                'title' => 'Test',
                'content' => 'Content'
            ])->assertRedirect();
            $successfulTasks++;
        } catch (\Exception $e) {}
        
        // Tarea 3: Editar perfil
        try {
            $this->put('/profile', [
                'name' => 'Updated Name'
            ])->assertRedirect();
            $successfulTasks++;
        } catch (\Exception $e) {}
        
        // ... más tareas
        
        $completionRate = ($successfulTasks / $totalTasks) * 100;
        
        $this->assertGreaterThanOrEqual(90, $completionRate, 
            'Tasa de éxito debe ser mayor al 90%');
    }

    /**
     * Métrica 3: Tasa de Error
     * Objetivo: < 5% de errores de usuario
     */
    public function test_error_rate_is_low()
    {
        $user = User::factory()->create();
        $totalActions = 20;
        $errors = 0;
        
        // Simular 20 acciones comunes
        for ($i = 0; $i < $totalActions; $i++) {
            try {
                $this->actingAs($user)->get('/dashboard');
            } catch (\Exception $e) {
                $errors++;
            }
        }
        
        $errorRate = ($errors / $totalActions) * 100;
        
        $this->assertLessThan(5, $errorRate, 'Tasa de error debe ser menor al 5%');
    }

    /**
     * Métrica 4: Tiempo en Tarea (Time on Task)
     * Objetivo: Tareas comunes en < 2 minutos
     */
    public function test_common_tasks_complete_quickly()
    {
        $user = User::factory()->create();
        
        // Medir tiempo para crear un post
        $startTime = microtime(true);
        
        $this->actingAs($user)->get('/posts/create');
        $this->post('/posts', [
            'title' => 'Quick Post',
            'content' => 'Content here'
        ]);
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        // En un test real, esto incluiría la interacción del usuario
        $this->assertLessThan(2, $duration);
    }

    /**
     * Métrica 5: Accesibilidad (WCAG 2.1)
     */
    public function test_pages_have_proper_accessibility_attributes()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/posts/create');
        
        $content = $response->getContent();
        
        // Verificar elementos de accesibilidad
        $this->assertStringContainsString('aria-label', $content, 
            'Debe tener labels ARIA para lectores de pantalla');
        
        $this->assertStringContainsString('alt=', $content, 
            'Las imágenes deben tener texto alternativo');
        
        $this->assertStringContainsString('<label', $content, 
            'Los inputs deben tener labels asociados');
    }

    /**
     * Métrica 6: Responsive Design
     */
    public function test_interface_is_mobile_friendly()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $content = $response->getContent();
        
        // Verificar viewport meta tag
        $this->assertStringContainsString('viewport', $content);
        
        // Verificar clases responsive (Bootstrap/Tailwind)
        $this->assertTrue(
            str_contains($content, 'col-md-') || 
            str_contains($content, 'sm:') ||
            str_contains($content, 'responsive'),
            'Debe tener clases responsive'
        );
    }
}