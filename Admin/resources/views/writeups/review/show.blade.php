<x-app-layout>
    <x-slot name="header">
        Review Writeup
    </x-slot>

    <x-slot name="subheader">
        Review the student submission, make permitted corrections, and update its review status.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-pencil-square"></i>
    </x-slot>

    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item">
            Writeups
        </li>

        <li class="breadcrumb-item">
            <a href="{{ route('writeups.review.index') }}">
                Review Queue
            </a>
        </li>

        <li class="breadcrumb-item active">
            Review
        </li>
    </x-slot>

    <div class="cyb-page">
        <livewire:writeup-review-detail :writeup="$writeup" />
    </div>
</x-app-layout>