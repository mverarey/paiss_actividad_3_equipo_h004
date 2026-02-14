<!-- ❌ DISEÑO RECARGADO -->
<!--
<div class="dashboard" style="background: linear-gradient(45deg, #ff00ff, #00ffff); padding: 50px;">
    <h1 style="font-size: 48px; color: gold; text-shadow: 5px 5px 10px black;">
        ¡¡¡BIENVENIDO A TU DASHBOARD INCREÍBLE!!!
    </h1>
    <p>Hoy es {{ now()->format('l, d \d\e F \d\e Y') }} y son las {{ now()->format('H:i:s') }}</p>
    <p>Tu último login fue hace {{ auth()->user()->last_login_at->diffForHumans() }}</p>
    <p>Tu IP es: {{ request()->ip() }}</p>
    <p>Navegador: {{ request()->userAgent() }}</p>
    ... mucha información innecesaria ...
</div>
-->

<!-- ✅ DISEÑO MINIMALISTA Y LIMPIO -->
<div class="dashboard">
    <!-- ✅ Saludo simple y claro -->
    <div class="mb-4">
        <h1>Bienvenido, {{ auth()->user()->first_name }}</h1>
        <p class="text-muted">{{ now()->format('d M Y') }}</p>
    </div>

    <!-- ✅ Información esencial en cards limpios -->
    <div class="row g-3">
        <!-- ✅ KPI 1: Solo lo importante -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Posts Publicados</h6>
                    <h2>{{ $publishedPosts }}</h2>
                    <small class="text-success">+12% este mes</small>
                </div>
            </div>
        </div>

        <!-- ✅ KPI 2 -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Vistas Totales</h6>
                    <h2>{{ number_format($totalViews) }}</h2>
                    <small class="text-success">+8% esta semana</small>
                </div>
            </div>
        </div>

        <!-- ✅ KPI 3 -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Comentarios</h6>
                    <h2>{{ $commentsCount }}</h2>
                    <small class="text-info">{{ $pendingComments }} pendientes</small>
                </div>
            </div>
        </div>

        <!-- ✅ KPI 4 -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Borradores</h6>
                    <h2>{{ $draftsCount }}</h2>
                    <a href="{{ route('posts.index', ['status' => 'draft']) }}">Ver todos</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Actividad reciente: Solo últimas 5 -->
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Actividad Reciente</h5>
            <a href="{{ route('activity.all') }}" class="btn btn-sm btn-link">Ver todo</a>
        </div>
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                @foreach($recentActivity->take(5) as $activity)
                <li class="mb-2">
                    <span class="text-muted">{{ $activity->created_at->diffForHumans() }}</span>
                    <span>{{ $activity->description }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- ✅ Acciones rápidas: Solo las más usadas -->
    <div class="mt-4">
        <h5>Acciones Rápidas</h5>
        <div class="btn-group" role="group">
            <a href="{{ route('posts.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Nuevo Post
            </a>
            <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list"></i> Mis Posts
            </a>
            <a href="{{ route('comments.pending') }}" class="btn btn-outline-secondary">
                <i class="bi bi-chat"></i> Comentarios
                @if($pendingComments > 0)
                    <span class="badge bg-danger">{{ $pendingComments }}</span>
                @endif
            </a>
        </div>
    </div>
</div>

<style>
/* ✅ ESTÉTICA MINIMALISTA */
.dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: none; /* ✅ Sin sombras excesivas */
    transition: box-shadow 0.2s;
}

.card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); /* ✅ Efecto sutil */
}

.card-body {
    padding: 1.5rem;
}

/* ✅ Tipografía clara y legible */
h1 {
    font-size: 2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0.5rem 0;
}

h6 {
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ✅ Espaciado generoso pero no excesivo */
.mb-4 { margin-bottom: 1.5rem; }
.mt-4 { margin-top: 1.5rem; }
</style>