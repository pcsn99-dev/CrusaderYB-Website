@props(['name', 'size' => 40])

@php
    $initials = collect(explode(' ', $name))
                        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                        ->take(2)
                        ->join('');    

@endphp

<div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center shadow"
     style="width: {{ $size }}px; height: {{ $size }}px;">
    {{ $initials }}
</div>