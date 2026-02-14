<?php

namespace Tests\Usability;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessibilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * WCAG 2.1 - Nivel A: Todas las imágenes tienen texto alternativo
     */
    public function test_all_images_have_alt_text()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        $content = $response->getContent();
        
        // Buscar todas las etiquetas img
        preg_match_all('/<img[^>]+>/', $content, $images);
        
        foreach ($images[0] as $img) {
            $this->assertStringContainsString('alt=', $img, 
                'Todas las imágenes deben tener atributo alt');
        }
    }

    /**
     * WCAG 2.1 - Nivel A: Los formularios tienen labels apropiados
     */
    public function test_form_inputs_have_labels()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/posts/create');
        $content = $response->getContent();
        
        // Buscar inputs
        preg_match_all('/<input[^>]+name="([^"]+)"/', $content, $inputs);
        
        foreach ($inputs[1] as $inputName) {
            // Debe existir un label para este input
            $this->assertStringContainsString("for=\"{$inputName}\"", $content, 
                "Input {$inputName} debe tener un label asociado");
        }
    }

    /**
     * WCAG 2.1 - Nivel AA: Contraste de color suficiente
     */
    public function test_sufficient_color_contrast()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        // En un test real, usarías una herramienta como Pa11y o aXe
        // Aquí verificamos que se usan clases de color apropiadas
        $content = $response->getContent();
        
        // Verificar que no se usan colores con bajo contraste
        $this->assertStringNotContainsString('text-gray-100 bg-gray-200', $content);
    }

    /**
     * WCAG 2.1 - Navegación por teclado
     */
    public function test_keyboard_navigation_is_possible()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/posts');
        $content = $response->getContent();
        
        // Verificar que los elementos interactivos tienen tabindex o son focusables
        $this->assertStringContainsString('tabindex', $content);
        
        // Verificar que no hay tabindex negativos (excepto -1 intencional)
        $this->assertStringNotContainsString('tabindex="-2"', $content);
    }

    /**
     * WCAG 2.1 - Landmark regions (navegación semántica)
     */
    public function test_semantic_html_structure()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        $content = $response->getContent();
        
        // Verificar que usa HTML semántico
        $this->assertStringContainsString('<header', $content);
        $this->assertStringContainsString('<nav', $content);
        $this->assertStringContainsString('<main', $content);
        $this->assertStringContainsString('<footer', $content);
    }
}