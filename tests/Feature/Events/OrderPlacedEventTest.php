<?php

namespace Tests\Feature\Events;

use Tests\TestCase;
use App\Events\OrderPlaced;
use App\Listeners\SendOrderConfirmation;
use App\Listeners\UpdateInventory;
use App\Models\Order;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderPlacedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_placed_event_is_dispatched()
    {
        Event::fake();
        
        $order = Order::factory()->create();
        
        event(new OrderPlaced($order));
        
        Event::assertDispatched(OrderPlaced::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }

    public function test_listeners_are_called_when_event_is_dispatched()
    {
        Event::fake();
        
        $order = Order::factory()->create();
        
        event(new OrderPlaced($order));
        
        Event::assertDispatched(OrderPlaced::class);
        Event::assertListening(OrderPlaced::class, SendOrderConfirmation::class);
        Event::assertListening(OrderPlaced::class, UpdateInventory::class);
    }

    public function test_send_order_confirmation_listener_sends_email()
    {
        $order = Order::factory()->create();
        $event = new OrderPlaced($order);
        $listener = new SendOrderConfirmation();
        
        \Mail::fake();
        
        $listener->handle($event);
        
        \Mail::assertSent(\App\Mail\OrderConfirmation::class);
    }

    public function test_update_inventory_listener_decreases_stock()
    {
        $order = Order::factory()->create();
        $product = \App\Models\Product::factory()->create(['stock' => 10]);
        $order->products()->attach($product, ['quantity' => 2]);
        
        $event = new OrderPlaced($order);
        $listener = new UpdateInventory();
        
        $listener->handle($event);
        
        $product->refresh();
        $this->assertEquals(8, $product->stock);
    }
}