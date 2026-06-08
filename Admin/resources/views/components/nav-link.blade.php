@props([
    'active' => false,
    'icon' => null,
    'child' => false,
])

@php
$classes = 'nav-link' . ($active ? ' active' : '') . ($child ? ' ps-5' : '');
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <i class="nav-icon {{ $icon }}"></i>
    @endif

    <p>{{ $slot }}</p>
</a>