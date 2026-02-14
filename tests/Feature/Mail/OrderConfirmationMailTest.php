<?php

namespace Tests\Feature\Mail;

use Tests\TestCase;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class OrderConfirmationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_confirmation_email_can_be_sent()
    {
        Mail::fake();
        
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        
        Mail::to($user)->send(new OrderConfirmation($order));
        
        Mail::assertSent(OrderConfirmation::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_order_confirmation_contains_correct_subject()
    {
        $order = Order::factory()->create();
        
        $mailable = new OrderConfirmation($order);
        
        $mailable->assertHasSubject('Order Confirmation #' . $order->id);
    }

    public function test_order_confirmation_contains_order_details()
    {
        $order = Order::factory()->create(['total' => 150.50]);
        
        $mailable = new OrderConfirmation($order);
        
        $mailable->assertSeeInHtml('Order #' . $order->id);
        $mailable->assertSeeInHtml('$150.50');
    }

    public function test_order_confirmation_has_attachment()
    {
        $order = Order::factory()->create();
        
        $mailable = new OrderConfirmation($order);
        
        $mailable->assertHasAttachment('/path/to/invoice.pdf');
    }

    public function test_order_confirmation_uses_correct_view()
    {
        $order = Order::factory()->create();
        
        $mailable = new OrderConfirmation($order);
        
        $this->assertEquals('emails.orders.confirmation', $mailable->view);
    }
}