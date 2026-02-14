<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Notifications\OrderShipped;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class OrderShippedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_sent_to_user()
    {
        Notification::fake();
        
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        
        $user->notify(new OrderShipped($order));
        
        Notification::assertSentTo($user, OrderShipped::class);
    }

    public function test_notification_sent_via_multiple_channels()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        
        $notification = new OrderShipped($order);
        $channels = $notification->via($user);
        
        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_notification_has_correct_mail_content()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['tracking_number' => 'TRACK123']);
        
        $notification = new OrderShipped($order);
        $mailData = $notification->toMail($user);
        
        $this->assertEquals('Your order has been shipped!', $mailData->subject);
        $this->assertStringContainsString('TRACK123', $mailData->introLines[0]);
    }

    public function test_notification_stores_data_in_database()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['tracking_number' => 'TRACK456']);
        
        $notification = new OrderShipped($order);
        $arrayData = $notification->toArray($user);
        
        $this->assertArrayHasKey('order_id', $arrayData);
        $this->assertArrayHasKey('tracking_number', $arrayData);
        $this->assertEquals('TRACK456', $arrayData['tracking_number']);
    }

    public function test_notification_not_sent_to_inactive_users()
    {
        Notification::fake();
        
        $inactiveUser = User::factory()->create(['is_active' => false]);
        $order = Order::factory()->create();
        
        if ($inactiveUser->is_active) {
            $inactiveUser->notify(new OrderShipped($order));
        }
        
        Notification::assertNotSentTo($inactiveUser, OrderShipped::class);
    }
}