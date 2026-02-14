<?php

namespace Tests\Integration\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Events\UserRegistered;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_registration_flow()
    {
        Mail::fake();
        Event::fake();
        
        // 1. Usuario se registra
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        
        // 2. Verificar redirección
        $response->assertRedirect('/dashboard');
        
        // 3. Verificar usuario en base de datos
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        // 4. Verificar que se envió email de bienvenida
        Mail::assertSent(WelcomeEmail::class, function ($mail) {
            return $mail->hasTo('john@example.com');
        });
        
        // 5. Verificar que se disparó evento
        Event::assertDispatched(UserRegistered::class);
        
        // 6. Verificar que el usuario está autenticado
        $this->assertAuthenticated();
    }

    public function test_complete_login_logout_flow()
    {
        // 1. Crear usuario
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        
        // 2. Intentar login
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        
        // 3. Verificar autenticación exitosa
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        
        // 4. Verificar sesión
        $this->assertNotNull(session('_token'));
        
        // 5. Acceder a ruta protegida
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        
        // 6. Logout
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/');
        
        // 7. Verificar que no está autenticado
        $this->assertGuest();
        
        // 8. Intentar acceder a ruta protegida
        $protectedResponse = $this->get('/dashboard');
        $protectedResponse->assertRedirect('/login');
    }

    public function test_password_reset_flow()
    {
        Mail::fake();
        
        $user = User::factory()->create(['email' => 'user@example.com']);
        
        // 1. Solicitar reset de contraseña
        $response = $this->post('/forgot-password', [
            'email' => 'user@example.com'
        ]);
        
        $response->assertStatus(200);
        
        // 2. Verificar que se envió email
        Mail::assertSent(\Illuminate\Auth\Notifications\ResetPassword::class);
        
        // 3. Obtener token del email (simulado)
        $token = app('auth.password.broker')->createToken($user);
        
        // 4. Reset de contraseña
        $resetResponse = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'user@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);
        
        $resetResponse->assertRedirect('/login');
        
        // 5. Verificar que la nueva contraseña funciona
        $loginResponse = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'newpassword123'
        ]);
        
        $this->assertAuthenticated();
    }

    public function test_email_verification_flow()
    {
        Mail::fake();
        
        // 1. Registrar usuario
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        
        $user = User::where('email', 'test@example.com')->first();
        
        // 2. Verificar que email no está verificado
        $this->assertNull($user->email_verified_at);
        
        // 3. Verificar que se envió email de verificación
        Mail::assertSent(\Illuminate\Auth\Notifications\VerifyEmail::class);
        
        // 4. Generar URL de verificación
        $verificationUrl = \URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        
        // 5. Visitar URL de verificación
        $verifyResponse = $this->actingAs($user)->get($verificationUrl);
        
        // 6. Verificar email verificado
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_two_factor_authentication_flow()
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_code' => '123456'
        ]);
        
        // 1. Login con credenciales correctas
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        // 2. Redirige a página de 2FA
        $response->assertRedirect('/two-factor-challenge');
        
        // 3. El usuario aún no está completamente autenticado
        $this->assertGuest();
        
        // 4. Enviar código 2FA
        $twoFactorResponse = $this->post('/two-factor-challenge', [
            'code' => '123456'
        ]);
        
        // 5. Ahora sí está autenticado
        $this->assertAuthenticated();
        $twoFactorResponse->assertRedirect('/dashboard');
    }
}