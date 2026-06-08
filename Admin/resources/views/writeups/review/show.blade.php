<x-app-layout>
     <x-slot name="header">
        Review Writeup
    </x-slot>
    
    <x-slot name="subheader">
        Reviewing for specific writeup
    </x-slot>

    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item">WriteUp</li>
        <li class="breadcrumb-item"> <a href="{{ route('writeups.review.index') }}">Queue</a></li>
        <li class="breadcrumb-item">Review</li>
    </x-slot>
    
    <livewire:writeup-review-detail :writeup="$writeup" />
        
</x-app-layout>