<?php

namespace Tests\Integration\Search;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_products_with_multiple_filters()
    {
        // Crear categorías
        $electronics = Category::factory()->create(['name' => 'Electronics']);
        $books = Category::factory()->create(['name' => 'Books']);
        
        // Crear productos
        $laptop = Product::factory()->create([
            'name' => 'Gaming Laptop',
            'price' => 1200,
            'category_id' => $electronics->id,
            'brand' => 'Dell',
            'in_stock' => true
        ]);
        
        $phone = Product::factory()->create([
            'name' => 'Smartphone',
            'price' => 800,
            'category_id' => $electronics->id,
            'brand' => 'Samsung',
            'in_stock' => true
        ]);
        
        $book = Product::factory()->create([
            'name' => 'Laravel Book',
            'price' => 50,
            'category_id' => $books->id,
            'brand' => 'Packt',
            'in_stock' => false
        ]);
        
        // 1. Búsqueda por nombre
        $searchResponse = $this->getJson('/api/products?search=laptop');
        $searchResponse->assertStatus(200);
        $searchResponse->assertJsonCount(1, 'data');
        $searchResponse->assertJsonFragment(['name' => 'Gaming Laptop']);
        
        // 2. Filtrar por categoría
        $categoryResponse = $this->getJson("/api/products?category_id={$electronics->id}");
        $categoryResponse->assertStatus(200);
        $categoryResponse->assertJsonCount(2, 'data');
        
        // 3. Filtrar por rango de precio
        $priceResponse = $this->getJson('/api/products?min_price=100&max_price=900');
        $priceResponse->assertStatus(200);
        $priceResponse->assertJsonCount(1, 'data');
        $priceResponse->assertJsonFragment(['name' => 'Smartphone']);
        
        // 4. Filtrar por disponibilidad
        $stockResponse = $this->getJson('/api/products?in_stock=true');
        $stockResponse->assertStatus(200);
        $stockResponse->assertJsonCount(2, 'data');
        
        // 5. Múltiples filtros combinados
        $combinedResponse = $this->getJson(
            "/api/products?category_id={$electronics->id}&min_price=1000&in_stock=true"
        );
        $combinedResponse->assertStatus(200);
        $combinedResponse->assertJsonCount(1, 'data');
        $combinedResponse->assertJsonFragment(['name' => 'Gaming Laptop']);
        
        // 6. Ordenamiento
        $sortResponse = $this->getJson('/api/products?sort=price&order=desc');
        $sortResponse->assertStatus(200);
        $firstProduct = $sortResponse->json('data.0');
        $this->assertEquals('Gaming Laptop', $firstProduct['name']);
    }

    public function test_search_with_relationships()
    {
        $category = Category::factory()->create(['name' => 'Tech']);
        $products = Product::factory()->count(3)->create([
            'category_id' => $category->id
        ]);
        
        // Agregar reseñas
        foreach ($products as $product) {
            $product->reviews()->create([
                'rating' => 5,
                'comment' => 'Excellent product',
                'user_id' => \App\Models\User::factory()->create()->id
            ]);
        }
        
        // Buscar con relaciones cargadas
        $response = $this->getJson('/api/products?include=category,reviews');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'category' => ['id', 'name'],
                    'reviews' => [
                        '*' => ['id', 'rating', 'comment']
                    ]
                ]
            ]
        ]);
    }

    public function test_full_text_search()
    {
        Product::factory()->create([
            'name' => 'Professional Camera',
            'description' => 'High quality DSLR camera for professional photography'
        ]);
        
        Product::factory()->create([
            'name' => 'Basic Webcam',
            'description' => 'Simple webcam for video calls'
        ]);
        
        // Búsqueda en múltiples campos
        $response = $this->getJson('/api/products?q=professional+photography');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['name' => 'Professional Camera']);
    }
}