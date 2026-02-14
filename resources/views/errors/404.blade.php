<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>404 - Página no encontrada</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #f8f9fa;
        }
        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #dc3545;
            margin: 0;
        }
        h1 {
            font-size: 2rem;
            margin: 1rem 0;
            color: #333;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin: 1rem 0;
        }
        .suggestions {
            text-align: left;
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .suggestions h3 {
            margin-top: 0;
            color: #333;
        }
        .suggestions ul {
            margin: 1rem 0 0 0;
            padding-left: 1.5rem;
        }
        .suggestions li {
            margin: 0.5rem 0;
            color: #555;
        }
        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <!-- ✅ Código de error visible pero no intimidante -->
        <div class="error-code">404</div>
        
        <!-- ✅ Título claro y humano -->
        <h1>{{ $title ?? 'Página no encontrada' }}</h1>
        
        <!-- ✅ Explicación en lenguaje natural -->
        <p>{{ $message ?? 'Lo sentimos, no pudimos encontrar la página que buscas.' }}</p>

        <!-- ✅ SUGERENCIAS CONSTRUCTIVAS -->
        <div class="suggestions">
            <h3>¿Qué puedes hacer?</h3>
            <ul>
                @foreach($suggestions ?? [] as $suggestion)
                <li>{{ $suggestion }}</li>
                @endforeach
            </ul>
        </div>

        <!-- ✅ ACCIONES CLARAS -->
        <div class="actions">
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="bi bi-house"></i> Ir al inicio
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver atrás
            </a>
        </div>

        <!-- ✅ Opción de buscar -->
        <div style="margin-top: 2rem;">
            <form action="{{ route('search') }}" method="GET">
                <input 
                    type="text" 
                    name="q" 
                    placeholder="¿Qué estabas buscando?"
                    style="padding: 0.75rem; width: 100%; max-width: 400px; border: 1px solid #ddd; border-radius: 6px;"
                >
            </form>
        </div>
    </div>
</body>
</html>