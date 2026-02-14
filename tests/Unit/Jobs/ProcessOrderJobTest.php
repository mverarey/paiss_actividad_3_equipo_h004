<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\ProcessOrder;
use App\Models\Order;
use App\Mail\OrderConfirmation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;

class ProcessOrderJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_dispatched_to_queue()
    {
        Queue::fake();
        
        $order = Order::factory()->create();
        
        ProcessOrder::dispatch($order);
        
        Queue::assertPushed(ProcessOrder::class, function ($job) use ($order) {
            return $job->order->id === $order->id;
        });
    }

    public function test_job_processes_order_correctly()
    {
        Mail::fake();
        
        $order = Order::factory()->create(['status' => 'pending']);
        
        $job = new ProcessOrder($order);
        $job->handle();
        
        $order->refresh();
        $this->assertEquals('processed', $order->status);
        
        Mail::assertSent(OrderConfirmation::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id;
        });
    }

    public function test_job_retries_on_failure()
    {
        $order = Order::factory()->create();
        $job = new ProcessOrder($order);
        
        $this->assertEquals(3, $job->tries);
    }

    public function test_failed_job_marks_order_as_failed()
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $job = new ProcessOrder($order);
        
        $exception = new \Exception('Payment gateway error');
        $job->failed($exception);
        
        $order->refresh();
        $this->assertEquals('failed', $order->status);
    }
}