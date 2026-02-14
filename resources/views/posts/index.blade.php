<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Posts</title>
</head>
<body>
    <div class="container">
        <!-- ✅ FLEXIBILIDAD: Barra de búsqueda con atajos -->
        <div class="search-container">
            <input 
                type="text" 
                id="quick-search" 
                placeholder="Buscar posts... (Ctrl + K)"
                class="form-control"
            >
            <!-- ✅ Indicador de atajo visible -->
            <span class="keyboard-shortcut">Ctrl+K</span>
        </div>

        <!-- ✅ EFICIENCIA: Acciones rápidas para expertos -->
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h1>Posts</h1>
                <!-- ✅ Mostrar atajos disponibles -->
                <small class="text-muted">
                    Atajos: 
                    <kbd>N</kbd> Nuevo post | 
                    <kbd>/</kbd> Buscar | 
                    <kbd>?</kbd> Ayuda
                </small>
            </div>
            
            <div>
                <!-- ✅ Botón normal para novatos -->
                <a href="{{ route('posts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Nuevo Post
                </a>
                
                <!-- ✅ Acciones masivas para usuarios avanzados -->
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        Acciones masivas
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="bulkPublish()">Publicar seleccionados</a></li>
                        <li><a class="dropdown-item" href="#" onclick="bulkArchive()">Archivar seleccionados</a></li>
                        <li><a class="dropdown-item" href="#" onclick="bulkDelete()">Eliminar seleccionados</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="bulkExport()">Exportar seleccionados</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ✅ FLEXIBILIDAD: Vista de tabla O tarjetas -->
        <div class="view-switcher mb-3">
            <button onclick="switchView('table')" data-shortcut="Alt+1">
                <i class="bi bi-list"></i> Lista
            </button>
            <button onclick="switchView('grid')" data-shortcut="Alt+2">
                <i class="bi bi-grid"></i> Cuadrícula
            </button>
        </div>

        <!-- ✅ EFICIENCIA: Selección múltiple con Shift -->
        <table class="table" id="posts-table">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select-all">
                    </th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $post)
                <tr data-id="{{ $post->id }}">
                    <td>
                        <input type="checkbox" class="post-checkbox" value="{{ $post->id }}">
                    </td>
                    <td>
                        <!-- ✅ Doble clic para editar (usuarios avanzados) -->
                        <a href="{{ route('posts.show', $post) }}" ondblclick="editPost({{ $post->id }})">
                            {{ $post->title }}
                        </a>
                    </td>
                    <td>{{ $post->author->name }}</td>
                    <td>{{ $post->created_at->format('d/m/Y') }}</td>
                    <td>
                        <!-- ✅ Acciones rápidas con iconos -->
                        <a href="{{ route('posts.edit', $post) }}" title="Editar (E)">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="{{ route('posts.show', $post) }}" title="Ver (V)">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- ✅ FLEXIBILIDAD: Paginación con opciones -->
        <div class="d-flex justify-content-between">
            {{ $posts->links() }}
            
            <!-- ✅ Usuarios expertos pueden cambiar items por página -->
            <div>
                <label>Mostrar:</label>
                <select onchange="changePerPage(this.value)">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
        </div>
    </div>

    <!-- ✅ EFICIENCIA: Modal de ayuda de atajos (?) -->
    <div class="modal fade" id="shortcutsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Atajos de Teclado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <tr>
                            <td><kbd>Ctrl</kbd> + <kbd>K</kbd></td>
                            <td>Abrir búsqueda rápida</td>
                        </tr>
                        <tr>
                            <td><kbd>N</kbd></td>
                            <td>Crear nuevo post</td>
                        </tr>
                        <tr>
                            <td><kbd>E</kbd></td>
                            <td>Editar post seleccionado</td>
                        </tr>
                        <tr>
                            <td><kbd>/</kbd></td>
                            <td>Enfocar buscador</td>
                        </tr>
                        <tr>
                            <td><kbd>Shift</kbd> + Click</td>
                            <td>Selección múltiple</td>
                        </tr>
                        <tr>
                            <td><kbd>Alt</kbd> + <kbd>1/2</kbd></td>
                            <td>Cambiar vista</td>
                        </tr>
                        <tr>
                            <td><kbd>?</kbd></td>
                            <td>Mostrar esta ayuda</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    // ✅ EFICIENCIA: Implementar atajos de teclado
    document.addEventListener('keydown', function(e) {
        // Ctrl + K: Búsqueda rápida
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            document.getElementById('quick-search').focus();
        }
        
        // N: Nuevo post
        if (e.key === 'n' && !isTyping()) {
            window.location.href = '{{ route("posts.create") }}';
        }
        
        // /: Enfocar búsqueda
        if (e.key === '/' && !isTyping()) {
            e.preventDefault();
            document.getElementById('quick-search').focus();
        }
        
        // ?: Mostrar ayuda
        if (e.key === '?' && !isTyping()) {
            new bootstrap.Modal(document.getElementById('shortcutsModal')).show();
        }
        
        // E: Editar (si hay uno seleccionado)
        if (e.key === 'e' && !isTyping()) {
            const selected = document.querySelector('.post-checkbox:checked');
            if (selected) {
                const postId = selected.value;
                window.location.href = `/posts/${postId}/edit`;
            }
        }
    });

    // ✅ EFICIENCIA: Selección múltiple con Shift
    let lastChecked = null;
    const checkboxes = document.querySelectorAll('.post-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('click', function(e) {
            if (e.shiftKey && lastChecked) {
                const start = Array.from(checkboxes).indexOf(lastChecked);
                const end = Array.from(checkboxes).indexOf(this);
                const range = [start, end].sort((a, b) => a - b);
                
                checkboxes.forEach((cb, index) => {
                    if (index >= range[0] && index <= range[1]) {
                        cb.checked = lastChecked.checked;
                    }
                });
            }
            lastChecked = this;
        });
    });

    // ✅ Verificar si el usuario está escribiendo
    function isTyping() {
        const activeElement = document.activeElement;
        return activeElement.tagName === 'INPUT' || 
               activeElement.tagName === 'TEXTAREA' ||
               activeElement.isContentEditable;
    }

    // ✅ FLEXIBILIDAD: Guardar preferencias de usuario
    function switchView(view) {
        localStorage.setItem('preferred_view', view);
        // Aplicar vista...
    }

    function changePerPage(value) {
        const url = new URL(window.location);
        url.searchParams.set('per_page', value);
        window.location = url;
    }
    </script>
</body>
</html>