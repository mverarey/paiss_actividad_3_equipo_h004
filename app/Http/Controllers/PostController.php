<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        // ✅ FLEXIBILIDAD: Permitir diferentes formatos de respuesta
        $posts = Post::with('author')
            ->when($request->search, function($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->category, function($query, $category) {
                $query->where('category_id', $category);
            })
            ->when($request->sort, function($query, $sort) {
                // ✅ Usuarios avanzados pueden ordenar por múltiples campos
                $query->orderBy($sort, $request->order ?? 'asc');
            })
            ->paginate($request->per_page ?? 10);

        // ✅ EFICIENCIA: Responder en formato solicitado
        if ($request->wantsJson()) {
            return response()->json($posts);
        }

        // ✅ EFICIENCIA: API para usuarios avanzados
        if ($request->format === 'csv') {
            return $this->exportCsv($posts);
        }

        return view('posts.index', compact('posts'));
    }

    // ✅ EFICIENCIA: Endpoint para acciones masivas
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:publish,archive,delete',
            'post_ids' => 'required|array',
            'post_ids.*' => 'exists:posts,id'
        ]);

        $posts = Post::whereIn('id', $validated['post_ids']);

        switch ($validated['action']) {
            case 'publish':
                $posts->update(['status' => 'published']);
                break;
            case 'archive':
                $posts->update(['status' => 'archived']);
                break;
            case 'delete':
                $posts->delete();
                break;
        }

        return response()->json([
            'message' => 'Acción completada exitosamente',
            'affected' => count($validated['post_ids'])
        ]);
    }
}