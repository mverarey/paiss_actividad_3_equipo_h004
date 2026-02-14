<!-- ✅ PÁGINA DE DETALLE MINIMALISTA -->
<article class="post-detail">
    <!-- ✅ Encabezado limpio -->
    <header class="mb-4">
        <h1>{{ $post->title }}</h1>
        
        <!-- ✅ Meta información esencial -->
        <div class="post-meta text-muted">
            <span>Por {{ $post->author->name }}</span>
            <span>•</span>
            <span>{{ $post->published_at->format('d M Y') }}</span>
            <span>•</span>
            <span>{{ $post->reading_time }} min de lectura</span>
        </div>
    </header>

    <!-- ✅ Imagen destacada (solo si existe) -->
    @if($post->featured_image)
    <figure class="mb-4">
        <img 
            src="{{ $post->featured_image }}" 
            alt="{{ $post->title }}"
            class="img-fluid rounded"
        >
    </figure>
    @endif

    <!-- ✅ Contenido principal: foco en la lectura -->
    <div class="post-content">
        {!! $post->content !!}
    </div>

    <!-- ✅ Tags: solo si existen -->
    @if($post->tags->isNotEmpty())
    <footer class="post-footer mt-4">
        <div class="tags">
            @foreach($post->tags as $tag)
                <a href="{{ route('posts.tagged', $tag) }}" class="badge bg-light text-dark">
                    {{ $tag->name }}
                </a>
            @endforeach
        </div>
    </footer>
    @endif
</article>

<style>
/* ✅ DISEÑO CENTRADO EN EL CONTENIDO */
.post-detail {
    max-width: 700px; /* ✅ Ancho óptimo para lectura */
    margin: 0 auto;
    padding: 2rem 1rem;
}

.post-content {
    font-size: 1.125rem; /* ✅ Tamaño legible */
    line-height: 1.7; /* ✅ Espaciado de línea cómodo */
    color: #333;
}

.post-content p {
    margin-bottom: 1.5rem;
}

/* ✅ Sin decoraciones innecesarias */
.post-content img {
    max-width: 100%;
    height: auto;
    margin: 2rem 0;
}

/* ✅ Enfoque en la tipografía */
h1 {
    font-size: 2.5rem;
    line-height: 1.2;
    margin-bottom: 1rem;
}

.post-meta {
    font-size: 0.9rem;
    margin-bottom: 2rem;
}
</style>