<x-app-layout>
    <x-slot name="header">
        Writeup Review Queue
    </x-slot>

    <x-slot name="subheader">
        Review submitted student writeups, check content issues, and track proofreading status.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-pencil-square"></i>
    </x-slot>

    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item">
            Writeups
        </li>

        <li class="breadcrumb-item active">
            Review Queue
        </li>
    </x-slot>

    <div class="cyb-page">
        <livewire:writeup-review-queue />
    </div>
</x-app-layout>