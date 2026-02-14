<?php

namespace Tests\Integration\Webhooks;

use Tests\TestCase;
use App\Models\Webhook;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class WebhookIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_triggered_on_order_completed()
    {
        Http::fake([
            'https://webhook.example.com/*' => Http::response(['success' => true], 200)
        ]);
        
        // 1. Registrar webhook
        $webhook = Webhook::create([
            'url' => 'https://webhook.example.com/orders',
            'events' => ['order.completed'],
            'secret' => 'webhook_secret_123',
            'active' => true
        ]);
        
        // 2. Crear orden
        $order = Order::factory()->create(['status' => 'pending']);
        
        // 3. Completar orden (debería disparar webhook)
        $order->update(['status' => 'completed']);
        
        // 4. Verificar que se realizó la petición HTTP
        Http::assertSent(function ($request) use ($order) {
            return $request->url() === 'https://webhook.example.com/orders' &&
                   $request['event'] === 'order.completed' &&
                   $request['data']['order_id'] === $order->id;
        });
        
        // 5. Verificar firma del webhook
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Webhook-Signature');
        });
    }

    public function test_webhook_retry_on_failure()
    {
        Queue::fake();
        
        // Simular fallo en el primer intento
        Http::fake([
            'https://webhook.example.com/*' => Http::sequence()
                ->push(['error' => 'Server error'], 500)
                ->push(['error' => 'Server error'], 500)
                ->push(['success' => true], 200)
        ]);
        
        $webhook = Webhook::create([
            'url' => 'https://webhook.example.com/events',
            'events' => ['order.created'],
            'active' => true,
            'max_retries' => 3
        ]);
        
        $order = Order::factory()->create();
        
        // Disparar webhook
        app(\App\Services\WebhookService::class)->dispatch('order.created', $order);
        
        // Verificar que se encoló job de retry
        Queue::assertPushed(\App\Jobs\RetryWebhook::class);
    }

    public function test_webhook_delivery_log()
    {
        Http::fake([
            'https://webhook.example.com/*' => Http::response(['success' => true], 200)
        ]);
        
        $webhook = Webhook::create([
            'url' => 'https://webhook.example.com/events',
            'events' => ['user.created']
        ]);
        
        $user = \App\Models\User::factory()->create();
        
        // Disparar webhook
        app(\App\Services\WebhookService::class)->dispatch('user.created', $user);
        
        // Verificar log de entrega
        $this->assertDatabaseHas('webhook_deliveries', [
            'webhook_id' => $webhook->id,
            'event' => 'user.created',
            'status' => 'success',
            'response_code' => 200
        ]);
    }

    public function test_incoming_webhook_verification()
    {
        $payload = ['event' => 'payment.success', 'amount' => 100];
        $secret = 'shared_secret';
        
        $signature = hash_hmac('sha256', json_encode($payload), $secret);
        
        // Webhook con firma correcta
        $validResponse = $this->postJson('/webhooks/stripe', $payload, [
            'X-Stripe-Signature' => $signature
        ]);
        
        $validResponse->assertStatus(200);
        
        // Webhook con firma incorrecta
        $invalidResponse = $this->postJson('/webhooks/stripe', $payload, [
            'X-Stripe-Signature' => 'invalid_signature'
        ]);
        
        $invalidResponse->assertStatus(401);
    }
}