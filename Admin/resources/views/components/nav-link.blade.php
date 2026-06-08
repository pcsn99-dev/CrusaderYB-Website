@props([
    'active' => false,
    'icon' => null,
])

@php
$classes = 'nav-link' . ($active ? ' active' : '');
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <i class="nav-icon {{ $icon }}"></i>
    @endif

    <p>
        {{ $slot }}
    </p>
</a>