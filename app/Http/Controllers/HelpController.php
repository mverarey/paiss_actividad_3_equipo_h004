<?php

namespace App\Http\Controllers;

class HelpController extends Controller
{
    // ✅ Centro de ayuda principal
    public function index()
    {
        $articles = [
            'getting-started' => [
                'title' => 'Primeros Pasos',
                'articles' => [
                    ['title' => 'Cómo crear tu primer post', 'url' => route('help.first-post')],
                    ['title' => 'Configurar tu perfil', 'url' => route('help.profile')],
                    ['title' => 'Entender el dashboard', 'url' => route('help.dashboard')],
                ]
            ],
            'writing' => [
                'title' => 'Escribir Contenido',
                'articles' => [
                    ['title' => 'Guía de formato Markdown', 'url' => route('help.markdown')],
                    ['title' => 'Optimizar para SEO', 'url' => route('help.seo')],
                    ['title' => 'Agregar imágenes y multimedia', 'url' => route('help.media')],
                ]
            ],
            'advanced' => [
                'title' => 'Funciones Avanzadas',
                'articles' => [
                    ['title' => 'Usar atajos de teclado', 'url' => route('help.shortcuts')],
                    ['title' => 'Programar publicaciones', 'url' => route('help.scheduling')],
                    ['title' => 'Analíticas y estadísticas', 'url' => route('help.analytics')],
                ]
            ]
        ];

        return view('help.index', compact('articles'));
    }

    // ✅ Búsqueda en la ayuda
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        $results = HelpArticle::where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->get();

        return view('help.search', compact('results', 'query'));
    }

    // ✅ Artículo específico con navegación
    public function article($slug)
    {
        $article = HelpArticle::where('slug', $slug)->firstOrFail();
        
        // ✅ Artículos relacionados
        $related = HelpArticle::where('category', $article->category)
            ->where('id', '!=', $article->id)
            ->limit(3)
            ->get();

        return view('help.article', compact('article', 'related'));
    }
}