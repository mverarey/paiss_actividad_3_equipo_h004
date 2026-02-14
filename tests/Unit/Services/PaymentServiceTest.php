<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PaymentService;
use App\Models\Order;
use App\Exceptions\PaymentException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService();
    }

    public function test_calculate_total_with_tax()
    {
        $amount = 100;
        $taxRate = 0.21; // 21%
        
        $total = $this->paymentService->calculateTotalWithTax($amount, $taxRate);
        
        $this->assertEquals(121, $total);
    }

    public function test_process_payment_creates_transaction()
    {
        $order = Order::factory()->create(['total' => 100]);
        
        $result = $this->paymentService->processPayment($order, 'card', '4111111111111111');
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('transactions', [
            'order_id' => $order->id,
            'amount' => 100,
            'status' => 'completed'
        ]);
    }

    public function test_payment_rollback_on_failure()
    {
        $order = Order::factory()->create(['total' => 100]);
        
        DB::beginTransaction();
        
        try {
            $this->paymentService->processPayment($order, 'invalid_method', 'invalid_card');
        } catch (PaymentException $e) {
            DB::rollBack();
        }
        
        $this->assertDatabaseMissing('transactions', [
            'order_id' => $order->id
        ]);
    }

    public function test_refund_reduces_order_total()
    {
        $order = Order::factory()->create(['total' => 100, 'paid' => true]);
        $refundAmount = 30;
        
        $this->paymentService->refund($order, $refundAmount);
        
        $order->refresh();
        $this->assertEquals(70, $order->total);
        $this->assertDatabaseHas('refunds', [
            'order_id' => $order->id,
            'amount' => 30
        ]);
    }
}