<form method="POST" action="{{ route('users.store') }}">
    @csrf
    
    <!-- ✅ PREVENCIÓN: Type específico previene entrada incorrecta -->
    <div class="mb-3">
        <label for="email">Email</label>
        <input 
            type="email"                    <!-- ✅ Validación HTML5 -->
            class="form-control @error('email') is-invalid @enderror" 
            id="email" 
            name="email"
            value="{{ old('email') }}"
            required                        <!-- ✅ Campo obligatorio -->
            autocomplete="email"
            placeholder="usuario@ejemplo.com"
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <!-- ✅ PREVENCIÓN: Indicador de requisitos de contraseña -->
    <div class="mb-3">
        <label for="password">Contraseña</label>
        <input 
            type="password" 
            class="form-control" 
            id="password" 
            name="password"
            required
            minlength="8"                   <!-- ✅ HTML previene cortas -->
        >
        <!-- ✅ PREVENCIÓN: Mostrar requisitos claramente -->
        <small class="form-text">
            La contraseña debe tener:
            <ul class="mb-0 mt-1">
                <li id="length" class="text-danger">Mínimo 8 caracteres</li>
                <li id="uppercase" class="text-danger">Una mayúscula</li>
                <li id="lowercase" class="text-danger">Una minúscula</li>
                <li id="number" class="text-danger">Un número</li>
                <li id="symbol" class="text-danger">Un símbolo</li>
            </ul>
        </small>
    </div>
    
    <!-- ✅ PREVENCIÓN: Dropdown en lugar de texto libre -->
    <div class="mb-3">
        <label for="role">Rol</label>
        <select class="form-control" id="role" name="role" required>
            <option value="">-- Selecciona un rol --</option>
            <option value="admin">Administrador</option>
            <option value="editor">Editor</option>
            <option value="viewer">Lector</option>
        </select>
        <!-- ✅ No permite valores inválidos -->
    </div>
    
    <!-- ✅ PREVENCIÓN: Date picker en lugar de texto -->
    <div class="mb-3">
        <label for="birth_date">Fecha de Nacimiento</label>
        <input 
            type="date" 
            class="form-control" 
            id="birth_date" 
            name="birth_date"
            max="{{ date('Y-m-d') }}"      <!-- ✅ No permite fechas futuras -->
            min="1900-01-01"               <!-- ✅ Rango razonable -->
            required
        >
    </div>
    
    <!-- ✅ PREVENCIÓN: Confirmación antes de enviar -->
    <button 
        type="submit" 
        class="btn btn-primary"
        onclick="return confirm('¿Confirmas que los datos son correctos?')"
    >
        Crear Usuario
    </button>
</form>

<script>
// ✅ PREVENCIÓN: Validación en tiempo real
document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    
    // Actualizar indicadores visuales
    document.getElementById('length').className = 
        password.length >= 8 ? 'text-success' : 'text-danger';
    document.getElementById('uppercase').className = 
        /[A-Z]/.test(password) ? 'text-success' : 'text-danger';
    document.getElementById('lowercase').className = 
        /[a-z]/.test(password) ? 'text-success' : 'text-danger';
    document.getElementById('number').className = 
        /[0-9]/.test(password) ? 'text-success' : 'text-danger';
    document.getElementById('symbol').className = 
        /[^A-Za-z0-9]/.test(password) ? 'text-success' : 'text-danger';
});
</script>