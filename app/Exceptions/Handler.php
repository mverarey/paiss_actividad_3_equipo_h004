<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        // ✅ ERROR 404: Mensaje claro y útil
        if ($exception instanceof ModelNotFoundException || 
            $exception instanceof NotFoundHttpException) {
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Recurso no encontrado',
                    'message' => 'El elemento que buscas no existe o fue eliminado',
                    'suggestions' => [
                        'Verifica que la URL sea correcta',
                        'Regresa a la página anterior',
                        'Visita la página de inicio'
                    ],
                    'actions' => [
                        ['label' => 'Ir al inicio', 'url' => route('home')],
                        ['label' => 'Ver todos los posts', 'url' => route('posts.index')]
                    ]
                ], 404);
            }
            
            return response()->view('errors.404', [
                'title' => 'Página no encontrada',
                'message' => 'Lo sentimos, la página que buscas no existe.',
                'suggestions' => [
                    'Verifica que hayas escrito correctamente la URL',
                    'El contenido pudo haber sido movido o eliminado',
                    'Puedes usar el buscador para encontrar lo que necesitas'
                ]
            ], 404);
        }

        // ✅ ERROR DE AUTENTICACIÓN: Guiar al usuario
        if ($exception instanceof AuthenticationException) {
            return response()->view('errors.401', [
                'title' => 'Acceso no autorizado',
                'message' => 'Necesitas iniciar sesión para acceder a esta página',
                'action_url' => route('login'),
                'action_label' => 'Ir a inicio de sesión'
            ], 401);
        }

        // ✅ ERROR DE VALIDACIÓN: Ya manejado por Laravel de forma clara
        
        // ✅ ERROR 500: Mensaje amigable sin detalles técnicos
        if ($this->isHttpException($exception) && $exception->getStatusCode() == 500) {
            return response()->view('errors.500', [
                'title' => 'Error del servidor',
                'message' => 'Algo salió mal de nuestro lado',
                'suggestions' => [
                    'Intenta recargar la página',
                    'Si el problema persiste, contacta con soporte',
                    'Nuestro equipo ha sido notificado del problema'
                ],
                'support_email' => 'soporte@ejemplo.com'
            ], 500);
        }

        return parent::render($request, $exception);
    }
}