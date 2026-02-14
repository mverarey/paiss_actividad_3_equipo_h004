<?php

namespace Tests\Integration\Ecommerce;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Events\OrderPlaced;
use App\Jobs\ProcessPayment;
use App\Mail\OrderConfirmation;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_purchase_flow()
    {
        Mail::fake();
        Event::fake();
        Queue::fake();
        
        // 1. Crear usuario y productos
        $user = User::factory()->create();
        $product1 = Product::factory()->create([
            'name' => 'Product 1',
            'price' => 100,
            'stock' => 10
        ]);
        $product2 = Product::factory()->create([
            'name' => 'Product 2',
            'price' => 50,
            'stock' => 5
        ]);
        
        // 2. Agregar productos al carrito
        $this->actingAs($user)->post('/cart/add', [
            'product_id' => $product1->id,
            'quantity' => 2
        ]);
        
        $this->actingAs($user)->post('/cart/add', [
            'product_id' => $product2->id,
            'quantity' => 1
        ]);
        
        // 3. Verificar carrito
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 2
        ]);
        
        $cartTotal = (100 * 2) + (50 * 1); // 250
        
        // 4. Ver carrito
        $cartResponse = $this->actingAs($user)->get('/cart');
        $cartResponse->assertStatus(200);
        $cartResponse->assertSee('Product 1');
        $cartResponse->assertSee('Product 2');
        $cartResponse->assertSee('250');
        
        // 5. Proceder al checkout
        $checkoutResponse = $this->actingAs($user)->post('/checkout', [
            'shipping_address' => '123 Main St',
            'shipping_city' => 'New York',
            'shipping_zip' => '10001',
            'payment_method' => 'credit_card',
            'card_number' => '4111111111111111',
            'card_cvv' => '123',
            'card_expiry' => '12/25'
        ]);
        
        $checkoutResponse->assertStatus(200);
        
        // 6. Verificar que se creó la orden
        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(250, $order->total);
        $this->assertEquals('pending', $order->status);
        
        // 7. Verificar items de la orden
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 100
        ]);
        
        // 8. Verificar que se actualizó el inventario
        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(8, $product1->stock); // 10 - 2
        $this->assertEquals(4, $product2->stock); // 5 - 1
        
        // 9. Verificar que se vació el carrito
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id
        ]);
        
        // 10. Verificar que se disparó el evento
        Event::assertDispatched(OrderPlaced::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
        
        // 11. Verificar que se encoló el job de pago
        Queue::assertPushed(ProcessPayment::class, function ($job) use ($order) {
            return $job->order->id === $order->id;
        });
        
        // 12. Verificar que se envió email de confirmación
        Mail::assertSent(OrderConfirmation::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_checkout_fails_when_insufficient_stock()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 100,
            'stock' => 2
        ]);
        
        // Intentar agregar más del stock disponible
        $response = $this->actingAs($user)->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 5
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Insufficient stock']);
        
        // Verificar que no se agregó al carrito
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);
    }

    public function test_apply_discount_coupon_during_checkout()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100]);
        
        // Crear cupón
        $coupon = \App\Models\Coupon::create([
            'code' => 'SAVE20',
            'discount_percentage' => 20,
            'valid_until' => now()->addDays(7)
        ]);
        
        // Agregar al carrito
        $this->actingAs($user)->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);
        
        // Aplicar cupón
        $response = $this->actingAs($user)->post('/checkout', [
            'coupon_code' => 'SAVE20',
            'shipping_address' => '123 Main St',
            'payment_method' => 'credit_card'
        ]);
        
        // Verificar descuento aplicado
        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals(80, $order->total); // 100 - 20%
        $this->assertEquals('SAVE20', $order->coupon_code);
    }

    public function test_guest_checkout_flow()
    {
        Mail::fake();
        
        $product = Product::factory()->create(['price' => 50, 'stock' => 10]);
        
        // 1. Agregar al carrito como invitado (usando sesión)
        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);
        
        $response->assertStatus(200);
        
        // 2. Checkout como invitado
        $checkoutResponse = $this->post('/checkout/guest', [
            'email' => 'guest@example.com',
            'name' => 'Guest User',
            'shipping_address' => '456 Oak St',
            'shipping_city' => 'Boston',
            'shipping_zip' => '02101',
            'payment_method' => 'paypal'
        ]);
        
        // 3. Verificar orden creada
        $this->assertDatabaseHas('orders', [
            'email' => 'guest@example.com',
            'total' => 50,
            'is_guest' => true
        ]);
        
        // 4. Verificar email enviado
        Mail::assertSent(OrderConfirmation::class);
    }
}