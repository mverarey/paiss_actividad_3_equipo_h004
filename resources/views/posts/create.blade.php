<div class="container">
    <!-- ✅ BREADCRUMB para orientación -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('posts.index') }}">Posts</a></li>
            <li class="breadcrumb-item active">Crear Nuevo</li>
        </ol>
    </nav>

    <div class="row">
        <!-- ✅ FORMULARIO PRINCIPAL -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Crear Nuevo Post</h1>
                
                <!-- ✅ ACCESO RÁPIDO A AYUDA -->
                <button 
                    type="button" 
                    class="btn btn-outline-info btn-sm"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#helpSidebar"
                >
                    <i class="bi bi-question-circle"></i> ¿Necesitas ayuda?
                </button>
            </div>

            <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- ✅ TOOLTIPS CONTEXTUALES -->
                <div class="mb-3">
                    <label for="title" class="form-label">
                        Título
                        <i 
                            class="bi bi-info-circle text-muted" 
                            data-bs-toggle="tooltip" 
                            data-bs-placement="right"
                            title="Un buen título es descriptivo y atractivo. Aparecerá en los resultados de búsqueda."
                        ></i>
                    </label>
                    <input 
                        type="text" 
                        class="form-control @error('title') is-invalid @enderror" 
                        id="title" 
                        name="title"
                        placeholder="Ej: 10 consejos para mejorar tu productividad"
                        value="{{ old('title') }}"
                    >
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    
                    <!-- ✅ AYUDA INLINE -->
                    <small class="form-text text-muted">
                        Los títulos entre 50-70 caracteres funcionan mejor en redes sociales.
                        <a href="#" data-bs-toggle="modal" data-bs-target="#titleHelpModal">
                            Ver ejemplos
                        </a>
                    </small>
                </div>

                <!-- ✅ EDITOR CON GUÍA -->
                <div class="mb-3">
                    <label for="content" class="form-label">
                        Contenido
                        <button 
                            type="button" 
                            class="btn btn-link btn-sm p-0"
                            data-bs-toggle="modal"
                            data-bs-target="#markdownGuide"
                        >
                            <i class="bi bi-markdown"></i> Guía de formato
                        </button>
                    </label>
                    <textarea 
                        class="form-control @error('content') is-invalid @enderror" 
                        id="content" 
                        name="content" 
                        rows="15"
                    >{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- ✅ CAMPO CON EXPLICACIÓN EXPANDIBLE -->
                <div class="mb-3">
                    <label for="seo_description" class="form-label">
                        Descripción SEO (opcional)
                    </label>
                    <textarea 
                        class="form-control" 
                        id="seo_description" 
                        name="seo_description" 
                        rows="2"
                        maxlength="160"
                    >{{ old('seo_description') }}</textarea>
                    
                    <!-- ✅ ACORDEÓN CON MÁS INFO -->
                    <div class="accordion mt-2" id="seoHelp">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button 
                                    class="accordion-button collapsed" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#seoHelpContent"
                                >
                                    ¿Qué es la descripción SEO?
                                </button>
                            </h2>
                            <div id="seoHelpContent" class="accordion-collapse collapse" data-bs-parent="#seoHelp">
                                <div class="accordion-body">
                                    <p>La descripción SEO es el texto que aparece en los resultados de búsqueda de Google.</p>
                                    <strong>Consejos:</strong>
                                    <ul>
                                        <li>Mantén entre 120-160 caracteres</li>
                                        <li>Incluye palabras clave relevantes</li>
                                        <li>Haz que sea atractiva para hacer clic</li>
                                    </ul>
                                    <p class="mb-0">
                                        <a href="{{ route('help.seo') }}" target="_blank">
                                            Ver guía completa de SEO
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Publicar</button>
                    <button type="submit" name="action" value="draft" class="btn btn-secondary">
                        Guardar como borrador
                    </button>
                    <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>

        <!-- ✅ SIDEBAR CON CONSEJOS CONTEXTUALES -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightbulb"></i> Consejos Rápidos
                </div>
                <div class="card-body">
                    <h6>Para escribir un buen post:</h6>
                    <ol>
                        <li>Usa un título claro y descriptivo</li>
                        <li>Divide el contenido en secciones</li>
                        <li>Agrega imágenes relevantes</li>
                        <li>Revisa la ortografía antes de publicar</li>
                    </ol>
                    <a href="{{ route('help.writing-guide') }}" class="btn btn-sm btn-link">
                        Ver guía completa
                    </a>
                </div>
            </div>

            <!-- ✅ VIDEO TUTORIAL -->
            <div class="card mt-3">
                <div class="card-header">
                    <i class="bi bi-play-circle"></i> Tutorial en Video
                </div>
                <div class="card-body">
                    <p>¿Primera vez creando un post?</p>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#videoTutorial" class="btn btn-primary btn-sm">
                        Ver tutorial (2 min)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✅ OFFCANVAS: Panel de ayuda deslizable -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="helpSidebar">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Centro de Ayuda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <!-- ✅ BÚSQUEDA EN LA AYUDA -->
        <div class="mb-3">
            <input 
                type="search" 
                class="form-control" 
                placeholder="Buscar en la ayuda..."
                id="helpSearch"
            >
        </div>

        <!-- ✅ TEMAS COMUNES -->
        <h6>Temas Frecuentes</h6>
        <div class="list-group mb-3">
            <a href="#" class="list-group-item list-group-item-action">
                <i class="bi bi-pencil"></i> Cómo formatear texto
            </a>
            <a href="#" class="list-group-item list-group-item-action">
                <i class="bi bi-image"></i> Subir y optimizar imágenes
            </a>
            <a href="#" class="list-group-item list-group-item-action">
                <i class="bi bi-tags"></i> Usar etiquetas efectivamente
            </a>
            <a href="#" class="list-group-item list-group-item-action">
                <i class="bi bi-link"></i> Agregar enlaces
            </a>
        </div>

        <!-- ✅ CONTACTO DIRECTO -->
        <div class="card">
            <div class="card-body">
                <h6>¿Aún necesitas ayuda?</h6>
                <p class="small">Nuestro equipo está aquí para ti</p>
                <a href="{{ route('support') }}" class="btn btn-sm btn-primary">
                    Contactar soporte
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ✅ MODAL: Guía de Markdown -->
<div class="modal fade" id="markdownGuide" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Guía Rápida de Formato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Para hacer esto...</th>
                            <th>Escribe esto...</th>
                            <th>Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Título grande</td>
                            <td><code># Título</code></td>
                            <td><h3>Título</h3></td>
                        </tr>
                        <tr>
                            <td>Negrita</td>
                            <td><code>**texto**</code></td>
                            <td><strong>texto</strong></td>
                        </tr>
                        <tr>
                            <td>Cursiva</td>
                            <td><code>*texto*</code></td>
                            <td><em>texto</em></td>
                        </tr>
                        <tr>
                            <td>Lista</td>
                            <td><code>- Item 1<br>- Item 2</code></td>
                            <td><ul><li>Item 1</li><li>Item 2</li></ul></td>
                        </tr>
                        <tr>
                            <td>Enlace</td>
                            <td><code>[texto](url)</code></td>
                            <td><a href="#">texto</a></td>
                        </tr>
                    </tbody>
                </table>
                <a href="{{ route('help.markdown-full') }}" target="_blank">
                    Ver guía completa
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// ✅ Inicializar tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// ✅ Tutorial interactivo para nuevos usuarios
if (localStorage.getItem('first_post_visit') === null) {
    // Mostrar tour guiado
    startProductTour();
    localStorage.setItem('first_post_visit', 'true');
}
</script>