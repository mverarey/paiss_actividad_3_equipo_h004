<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // ✅ MINIMALISMO: Solo cargar datos que se mostrarán
        $data = [
            // ✅ Métricas esenciales
            'publishedPosts' => $user->posts()->published()->count(),
            'totalViews' => $user->posts()->sum('views_count'),
            'commentsCount' => $user->posts()->withCount('comments')->sum('comments_count'),
            'pendingComments' => $user->posts()
                ->join('comments', 'posts.id', '=', 'comments.post_id')
                ->where('comments.status', 'pending')
                ->count(),
            'draftsCount' => $user->posts()->draft()->count(),
            
            // ✅ Solo últimos 5 items
            'recentActivity' => $user->activities()->latest()->take(5)->get(),
        ];

        // ❌ NO cargar datos que no se usan:
        // - Todos los posts (solo necesitamos conteos)
        // - Historial completo de actividad
        // - Información del sistema
        // - Estadísticas detalladas

        return view('dashboard', $data);
    }
}