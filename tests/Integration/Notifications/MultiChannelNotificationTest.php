<?php

namespace Tests\Integration\Notifications;

use Tests\TestCase;
use App\Models\User;
use App\Notifications\ImportantAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;

class MultiChannelNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_sent_through_multiple_channels()
    {
        Notification::fake();
        
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'phone' => '+1234567890'
        ]);
        
        // Enviar notificación
        $user->notify(new ImportantAlert('System maintenance scheduled'));
        
        // Verificar que se envió la notificación
        Notification::assertSentTo($user, ImportantAlert::class);
        
        // Verificar canales utilizados
        Notification::assertSentTo($user, ImportantAlert::class, function ($notification, $channels) {
            return in_array('mail', $channels) &&
                   in_array('database', $channels) &&
                   in_array('sms', $channels);
        });
    }

    public function test_database_notification_storage_and_retrieval()
    {
        $user = User::factory()->create();
        
        // Crear notificación en base de datos
        $user->notify(new ImportantAlert('New message received'));
        
        // Verificar en base de datos
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'type' => ImportantAlert::class
        ]);
        
        // Obtener notificaciones no leídas
        $unreadNotifications = $user->unreadNotifications;
        $this->assertCount(1, $unreadNotifications);
        
        // Marcar como leída
        $notification = $unreadNotifications->first();
        $notification->markAsRead();
        
        // Verificar que fue marcada como leída
        $this->assertNotNull($notification->read_at);
        $this->assertCount(0, $user->unreadNotifications);
    }

    public function test_notification_preferences_respected()
    {
        Notification::fake();
        
        // Usuario con email deshabilitado
        $user = User::factory()->create([
            'notification_preferences' => [
                'email' => false,
                'sms' => true,
                'push' => true
            ]
        ]);
        
        $user->notify(new ImportantAlert('Test alert'));
        
        // Verificar que solo se usaron canales habilitados
        Notification::assertSentTo($user, ImportantAlert::class, function ($notification, $channels) {
            return !in_array('mail', $channels) &&
                   in_array('sms', $channels);
        });
    }

    public function test_batch_notification_to_multiple_users()
    {
        Notification::fake();
        
        $users = User::factory()->count(5)->create();
        
        // Enviar a múltiples usuarios
        Notification::send($users, new ImportantAlert('Broadcast message'));
        
        // Verificar que se envió a todos
        foreach ($users as $user) {
            Notification::assertSentTo($user, ImportantAlert::class);
        }
        
        // Verificar conteo
        Notification::assertSentTimes(ImportantAlert::class, 5);
    }

    public function test_queued_notification_processing()
    {
        Queue::fake();
        
        $user = User::factory()->create();
        
        // Enviar notificación en cola
        $user->notify(new ImportantAlert('Queued notification'));
        
        // Verificar que se encoló
        Queue::assertPushed(function ($job) {
            return $job instanceof \Illuminate\Notifications\SendQueuedNotifications;
        });
    }
}