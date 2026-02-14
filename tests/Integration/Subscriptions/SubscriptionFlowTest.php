<?php

namespace Tests\Integration\Subscriptions;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionCancelled;
use App\Mail\SubscriptionConfirmation;

class SubscriptionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_subscription_lifecycle()
    {
        Event::fake();
        Mail::fake();
        
        // 1. Crear planes
        $basicPlan = Plan::factory()->create([
            'name' => 'Basic',
            'price' => 9.99,
            'billing_period' => 'monthly'
        ]);
        
        $proPlan = Plan::factory()->create([
            'name' => 'Pro',
            'price' => 19.99,
            'billing_period' => 'monthly'
        ]);
        
        $user = User::factory()->create();
        
        // 2. Usuario se suscribe al plan básico
        $subscribeResponse = $this->actingAs($user)->postJson('/api/subscriptions', [
            'plan_id' => $basicPlan->id,
            'payment_method' => 'credit_card',
            'card_token' => 'tok_visa'
        ]);
        
        $subscribeResponse->assertStatus(201);
        
        // 3. Verificar suscripción creada
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $basicPlan->id,
            'status' => 'active'
        ]);
        
        $subscription = Subscription::where('user_id', $user->id)->first();
        
        // 4. Verificar evento disparado
        Event::assertDispatched(SubscriptionCreated::class);
        
        // 5. Verificar email de confirmación
        Mail::assertSent(SubscriptionConfirmation::class);
        
        // 6. Upgrade a plan Pro
        $upgradeResponse = $this->actingAs($user)->putJson("/api/subscriptions/{$subscription->id}/upgrade", [
            'plan_id' => $proPlan->id
        ]);
        
        $upgradeResponse->assertStatus(200);
        
        // 7. Verificar cambio de plan
        $subscription->refresh();
        $this->assertEquals($proPlan->id, $subscription->plan_id);
        
        // 8. Verificar cálculo prorrateado
        $this->assertNotNull($subscription->proration_amount);
        
        // 9. Cancelar suscripción
        $cancelResponse = $this->actingAs($user)->deleteJson("/api/subscriptions/{$subscription->id}");
        $cancelResponse->assertStatus(200);
        
        // 10. Verificar que está cancelada pero activa hasta fin de periodo
        $subscription->refresh();
        $this->assertEquals('cancelled', $subscription->status);
        $this->assertNotNull($subscription->ends_at);
        $this->assertTrue($subscription->onGracePeriod());
        
        // 11. Verificar evento de cancelación
        Event::assertDispatched(SubscriptionCancelled::class);
    }

    public function test_subscription_renewal_process()
    {
        Queue::fake();
        
        $plan = Plan::factory()->create(['price' => 9.99]);
        $user = User::factory()->create();
        
        // Crear suscripción que vence hoy
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'current_period_end' => now(),
            'status' => 'active'
        ]);
        
        // Ejecutar comando de renovación
        $this->artisan('subscriptions:renew')
             ->assertExitCode(0);
        
        // Verificar que se encoló job de cobro
        Queue::assertPushed(\App\Jobs\ChargeSubscription::class);
        
        // Simular cobro exitoso
        $subscription->refresh();
        $subscription->update([
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth()
        ]);
        
        // Verificar nueva factura
        $this->assertDatabaseHas('invoices', [
            'subscription_id' => $subscription->id,
            'amount' => 9.99,
            'status' => 'paid'
        ]);
    }

    public function test_failed_payment_handling()
    {
        Event::fake();
        Mail::fake();
        
        $user = User::factory()->create();
        $plan = Plan::factory()->create();
        
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active'
        ]);
        
        // Simular fallo de pago
        $failedPaymentResponse = $this->postJson('/webhooks/payment-failed', [
            'subscription_id' => $subscription->id,
            'error' => 'Insufficient funds'
        ]);
        
        // Verificar estado actualizado
        $subscription->refresh();
        $this->assertEquals('past_due', $subscription->status);
        
        // Verificar que se envió email de alerta
        Mail::assertSent(\App\Mail\PaymentFailed::class);
        
        // Después de 3 intentos fallidos, cancelar
        for ($i = 0; $i < 3; $i++) {
            $subscription->recordPaymentFailure();
        }
        
        $subscription->refresh();
        $this->assertEquals('cancelled', $subscription->status);
    }

    public function test_feature_access_based_on_subscription()
    {
        $basicPlan = Plan::factory()->create([
            'name' => 'Basic',
            'features' => ['feature_a', 'feature_b']
        ]);
        
        $proPlan = Plan::factory()->create([
            'name' => 'Pro',
            'features' => ['feature_a', 'feature_b', 'feature_c', 'premium_support']
        ]);
        
        $basicUser = User::factory()->create();
        $basicUser->subscriptions()->create([
            'plan_id' => $basicPlan->id,
            'status' => 'active'
        ]);
        
        $proUser = User::factory()->create();
        $proUser->subscriptions()->create([
            'plan_id' => $proPlan->id,
            'status' => 'active'
        ]);
        
        // Usuario básico intenta acceder a feature premium
        $basicResponse = $this->actingAs($basicUser)->getJson('/api/premium-feature');
        $basicResponse->assertStatus(403);
        
        // Usuario Pro puede acceder
        $proResponse = $this->actingAs($proUser)->getJson('/api/premium-feature');
        $proResponse->assertStatus(200);
    }
}