<form method="POST" action="{{ route('posts.update', $post) }}">
    @csrf
    @method('PUT')
    
    <!-- Campos del formulario -->
    
    <div class="d-flex gap-2">
        <!-- ✅ Guardar cambios -->
        <button type="submit" class="btn btn-primary">
            Guardar cambios
        </button>
        
        <!-- ✅ CANCELAR - Salida de emergencia -->
        <a href="{{ route('posts.show', $post) }}" class="btn btn-secondary">
            Cancelar
        </a>
        
        <!-- ✅ VER VISTA PREVIA - Sin guardar -->
        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#previewModal">
            Vista previa
        </button>
    </div>
</form>

<!-- ✅ CONFIRMACIÓN ANTES DE ACCIONES DESTRUCTIVAS -->
<button 
    type="button" 
    class="btn btn-danger"
    onclick="confirmDelete('{{ $post->title }}', '{{ route('posts.destroy', $post) }}')"
>
    <i class="bi bi-trash"></i> Eliminar
</button>

<script>
function confirmDelete(postTitle, deleteUrl) {
    // ✅ CONFIRMACIÓN CLARA con opción de cancelar
    if (confirm(`¿Estás seguro de eliminar "${postTitle}"?\n\nEsta acción no se puede deshacer.`)) {
        // Crear formulario y enviar
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = deleteUrl;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
    // ✅ Si cancela, no pasa nada - LIBERTAD
}
</script>