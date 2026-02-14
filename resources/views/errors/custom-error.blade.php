<!-- ✅ Componente reutilizable para mostrar errores -->
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <!-- ✅ Encabezado claro -->
    <div class="d-flex align-items-start">
        <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.5rem;"></i>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-2">Hay algunos problemas con tu formulario</h5>
            
            <!-- ✅ Lista de errores específicos -->
            <ul class="mb-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>

            <!-- ✅ Sugerencia general -->
            <p class="mb-0 small">
                <strong>Sugerencia:</strong> Revisa los campos marcados en rojo y corrige la información.
            </p>
        </div>
    </div>
    
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif