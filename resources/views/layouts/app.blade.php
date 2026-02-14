<!-- Mostrar feedback visual -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-x-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Loader durante procesos largos -->
<div id="loading-overlay" class="d-none">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Procesando...</span>
    </div>
    <p class="mt-3">Guardando cambios...</p>
</div>