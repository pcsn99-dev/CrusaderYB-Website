<x-app-layout>
    <x-slot name="header">
        Pictorial Schedules
    </x-slot>

    <x-slot name="subheader">
        Manage regular and delayed pictorial schedules for the active CYB year.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-calendar-week"></i>
    </x-slot>

    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item">
            Settings
        </li>

        <li class="breadcrumb-item active">
            Pictorial Schedules
        </li>
    </x-slot>

    <div class="cyb-page">
        <div
            id="pictorial-schedules-app"
            data-active-year='@json($activeYear)'
            data-colleges='@json($colleges)'
        ></div>
    </div>

    @push('scripts')
        @vite('resources/js/pictorial-schedules.ts')
    @endpush
</x-app-layout>