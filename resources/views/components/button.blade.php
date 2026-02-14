<!-- ✅ COMPONENTE REUTILIZABLE - Garantiza consistencia -->
<button 
    {{ $attributes->merge(['class' => "btn btn-{$type} btn-{$size}"]) }}
    type="{{ $attributes->get('type', 'button') }}"
>
    @if($icon)
        <i class="bi bi-{{ $icon }}"></i>
    @endif
    {{ $slot }}
</button>