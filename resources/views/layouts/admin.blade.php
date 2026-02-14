<!-- ✅ Estructura consistente para TODAS las páginas CRUD -->
@extends('layouts.app')

@section('content')
<div class="container">
    <!-- ✅ Siempre: Título a la izquierda, acciones a la derecha -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $title }}</h1>
        <div class="actions">
            {{ $headerActions ?? '' }}
        </div>
    </div>
    
    <!-- ✅ Siempre: Filtros arriba -->
    @if(isset($filters))
        <div class="filters mb-3">
            {{ $filters }}
        </div>
    @endif
    
    <!-- ✅ Siempre: Contenido principal -->
    <div class="content">
        {{ $slot }}
    </div>
    
    <!-- ✅ Siempre: Paginación abajo -->
    @if(isset($pagination))
        <div class="pagination-wrapper mt-4">
            {{ $pagination }}
        </div>
    @endif
</div>
@endsection